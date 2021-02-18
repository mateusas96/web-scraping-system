<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\ScrapingParam as SP;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Models\File;
use App\Models\ScrapingCategoryData AS SCD;

class SelectedFilesForScrapingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $scrapeEverything = $request->get('scrape_everything');
        $scrapingStatus = $request->get('scraping_status');
        $search = $request->get('query');

        return SFFS::select('selected_files_for_scraping_view.*')
                ->join(
                    'selected_files_for_scraping_view',
                    'selected_files_for_scrapings.uuid',
                    '=',
                    'selected_files_for_scraping_view.uuid'
                )->where(
                    'selected_files_for_scrapings.selected_by_user_id',
                    auth()->user()->id
                )->where(function($query) use ($search){
                    $query
                        ->where('selected_files_for_scraping_view.scraper_name', 'LIKE', "%$search%")
                        ->orWhere('selected_files_for_scraping_view.selected_files', 'LIKE', "%$search%")
                        ->orWhere('selected_files_for_scraping_view.scraping_params', 'LIKE', "%$search%")
                        ->orWhere('selected_files_for_scraping_view.started_scraping_date', 'LIKE', "%$search%")
                        ->orWhere('selected_files_for_scraping_view.stopped_scraping_date', 'LIKE', "%$search%")
                        ->orWhere('selected_files_for_scraping_view.scraper_created_at', 'LIKE', "%$search%");
                })->whereRaw(
                    '(
                        CASE WHEN ? IS NOT NULL AND ? <> "" THEN
                            selected_files_for_scrapings.scrape_all = ?
                        ELSE
                            1
                        END
                    )'
                , [$scrapeEverything, $scrapeEverything, $scrapeEverything])->whereRaw(
                    '(
                        CASE WHEN ? IS NOT NULL AND ? <> "" THEN
                            selected_files_for_scraping_view.scraping_status = ?
                        ELSE
                            1
                        END
                    )'
                , [$scrapingStatus, $scrapingStatus, $scrapingStatus])->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'scraper_name'  => 'unique:selected_files_for_scrapings,scraper_name,NULL,id,selected_by_user_id,' . auth()->user()->id,
        ]);

        $scraperName = $request->get('scraper_name');
        $scraperParamsObj = $request->get('scraper_params');
        $selectedFiles = $request->get('selected_files');
        $scrapeAll = $request->get('scrape_all');
        $scraperParams = null;

        if ($scrapeAll === null) {
            foreach($scraperParamsObj as $key => $value) {
                SP::create([
                    'user_id' => auth()->user()->id,
                    'scraper_name' => $scraperName,
                    'root_category' => $value['selected_root_category'],
                    'subcategory' => $value['subcategory'],
                    'product_name' => $value['name'],
                ]);
            }
        }

        $statusId = collect(
            DB::select('SELECT get_status_id_by_code("scraping_not_started") AS statusId')
        )->first()->statusId;
        
        SFFS::create([
            'scraper_name' => $scraperName,
            'selected_by_user_id' => auth()->user()->id,
            'selected_files_id' => implode(',', $request->get('selected_files')),
            'status_id' => $statusId,
            'scrape_all' => $scrapeAll === null ? false : $scrapeAll,
            'detailed_information_about_product' => $request->get('detailed_information_about_product'),
            'scraping_params' => $scraperParams,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Data inserted successfully',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SelectedFilesForScraping  $selectedFilesForScraping
     * @return \Illuminate\Http\Response
     */
    public function show(SelectedFilesForScraping $selectedFilesForScraping)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SelectedFilesForScraping  $selectedFilesForScraping
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SelectedFilesForScraping $selectedFilesForScraping)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  String scraper uuid $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $sffs = SFFS::select('scraper_name')->where('uuid', $uuid)->get()[0];

        SP::where('scraper_name', $sffs['scraper_name'])->delete();

        SFFS::where('uuid', $uuid)->delete();

        return response()->json([
            'error' => false,
            'message' => 'Scraper deleted successfully',
        ]); 
    }

    /**
     * Update selected scraper status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        $uuid = $request->get('uuid');
        $scraper_status = $request->get('status_code');
        $system = $request->get('system');

        $sffs = SFFS::findByUuid($uuid);
        $currentSffsStatusId = $sffs['status_id'];
        $sffsFilesId = $sffs['selected_files_id'];

        $files = File::select('id')->whereRaw('FIND_IN_SET(id, ?)', $sffsFilesId)->get();

        $statusId = collect(
            DB::select('SELECT get_status_id_by_code("' . $scraper_status . '") AS statusId')
        )->first()->statusId;

        $currentSffsStatusCode = collect(
            DB::select('SELECT get_status_code_by_id("' . $currentSffsStatusId . '") AS statusCode')
        )->first()->statusCode;

        if ($scraper_status == 'scraping_initiated') {
            $sffs->status_id = $statusId;
            $sffs->started_scraping_date = Carbon::now();
            $sffs->stopped_scraping_date = null;
            $sffs->save();
        }
        
        if (
            $scraper_status == 'scraping_stopped_for_a_reason' &&
            $system
        ) {
            foreach($files as $key => $value) {
                $temp_val = null;
                $temp_val = $value['id'];

                SFFS::where('selected_files_id', 'LIKE',  "%$temp_val%")
                    ->update([
                        'status_id' => $statusId,
                        'stopped_scraping_date' => Carbon::now()
                    ]);
            }
            SCD::where('scraper_name', $sffs['scraper_name'])->where('user_id', '=', auth()->user()-id)->delete();
        }

        if (
            $scraper_status == 'scraping_stopped_for_a_reason' ||
            $scraper_status == 'scraping_stopped_manually' &&
            !$system
        ) {
            if ($currentSffsStatusCode == 'scraping_initiated') {
                foreach($files as $key => $value) {
                    $temp_val = null;
                    $temp_val = $value['id'];

                    SFFS::where('selected_files_id', 'LIKE',  "%$temp_val%")
                        ->update([
                            'status_id' => $statusId,
                            'stopped_scraping_date' => Carbon::now()
                        ]);
                }
            } else {
                foreach($files as $key => $value) {
                    $temp_val = null;
                    $temp_val = $value['id'];
                    
                    SFFS::where('selected_files_id', 'LIKE',  "%$temp_val%")
                        ->update([
                            'status_id' => $statusId
                        ]);
                }

                SCD::where('scraper_name', $sffs['scraper_name'])->where('user_id', '=', auth()->user()-id)->delete();
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Scraper status updated',
        ]);
    }
}
