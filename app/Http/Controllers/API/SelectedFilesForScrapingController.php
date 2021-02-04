<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\ScrapingParam as SP;
use Illuminate\Http\Request;
use DB;

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
            'scraper_name'  => 'unique:selected_files_for_scrapings',
        ]);

        $scraperName = $request->get('scraper_name');
        $scraperParamsObj = $request->get('scraper_params');
        $selectedFiles = $request->get('selected_files');
        $scrapeAll = $request->get('scrape_all');
        $scraperParams = null;

        if ($scrapeAll === null) {
            foreach($scraperParamsObj as $key => $value) {
                SP::create([
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
}
