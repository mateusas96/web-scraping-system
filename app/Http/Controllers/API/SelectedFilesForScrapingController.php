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
use App\Services\StatusService;
use App\Models\ScrapingCategoryDataHarvest AS SCDH;
use App\Models\ScrapingProductScrape AS SPS;

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
                        ->orWhere('selected_files_for_scraping_view.finished_scraping_date', 'LIKE', "%$search%")
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
        $schedule = $request->get('schedule');
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
            'schedule' => $schedule,
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

        SP::where('scraper_name', $sffs['scraper_name'])->where('user_id', auth()->user()->id)->delete();

        SCDH::where('scraper_name', $sffs['scraper_name'])->where('user_id', auth()->user()->id)->delete();

        SPS::where('scraper_name', $sffs['scraper_name'])->where('user_id', auth()->user()->id)->delete();

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

        $statusService = new StatusService();

        $statusService->updateScraperStatus($uuid, $scraper_status);

        return response()->json([
            'error' => false,
            'message' => 'Scraper status updated',
        ]);
    }

    /**
     * Stop scraper and update it's status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeScraperStoppedStatus(Request $request)
    {
        $uuid = $request->get('uuid');
        $status_code = $request->get('status_code');

        if ($status_code == 'scraping_not_started' || $status_code == 'scraping_stopped_manually') {

            $status_id = collect(
                DB::select('SELECT get_status_id_by_code("' . $status_code . '") AS statusId')
            )->first()->statusId;

            SFFS::where('uuid', $uuid)
                ->update([
                    'status_id' => $status_id,
                    'scraper_stopped' => $status_code == 'scraping_stopped_manually' ? 1 : 0
                ]);
            
            return response()->json([
                'error' => false,
                'message' => 'Scraper stopped',
            ]);

        }
        
        return response([
            'error' => false,
            'message' => 'Wrong status code',
        ], 400); 
    }
}
