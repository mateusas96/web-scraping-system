<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SelectedFilesForScraping as SFFS;
use Illuminate\Http\Request;
use DB;

class SelectedFilesForScrapingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SFFS::select('selected_files_for_scraping_view.*')
                ->join(
                    'selected_files_for_scraping_view',
                    'selected_files_for_scrapings.uuid',
                    '=',
                    'selected_files_for_scraping_view.uuid'
                )->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $scraperName = $request->get('scraper_name');
        $scraperParamsObj = $request->get('scraper_params');
        $selectedFiles = $request->get('selected_files');
        $scrapeAll = $request->get('scrape_all');
        $scraperParams = null;

        if ($scrapeAll === null) {
            foreach($scraperParamsObj as $key => $value) {
                if ($key + 1 !== count($scraperParamsObj)) {
                    $scraperParams = $scraperParams === null ? 
                        '"' . $value['name'] . '", ' :
                        $scraperParams . '"' . $value['name'] . '", ';
                } else {
                    $scraperParams = $scraperParams . '"' . $value['name'] . '"';
                }
            }
        }

        $statusId = collect(
            DB::select('SELECT get_status_id_by_code("scraping_not_started") as statusId')
        )->first()->statusId;
        
        SFFS::create([
            'scraper_name' => $scraperName,
            'selected_by_user_id' => auth()->user()->id,
            'selected_files_id' => implode(',', $request->get('selected_files')),
            'status_id' => $statusId,
            'scrape_all' => $scrapeAll === null ? false : $scrapeAll,
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
     * @param  \App\Models\SelectedFilesForScraping  $selectedFilesForScraping
     * @return \Illuminate\Http\Response
     */
    public function destroy(SelectedFilesForScraping $selectedFilesForScraping)
    {
        //
    }

    /**
     * Search for file in data base
     * 
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request){
        if($search = $request->get('query')){
            $myFiles = SFFS::select('selected_files_for_scraping_view.*')
            ->join(
                'selected_files_for_scraping_view',
                'selected_files_for_scrapings.uuid',
                '=',
                'selected_files_for_scraping_view.uuid'
            )->where(function($query) use ($search){
                $query
                    ->where('selected_files_for_scraping_view.scraper_name', 'LIKE', "%$search%")
                    ->orWhere('selected_files_for_scraping_view.selected_files', 'LIKE', "%$search%")
                    ->orWhere('selected_files_for_scraping_view.scraping_params', 'LIKE', "%$search%")
                    ->orWhere('selected_files_for_scraping_view.started_scraping_date', 'LIKE', "%$search%")
                    ->orWhere('selected_files_for_scraping_view.stopped_scraping_date', 'LIKE', "%$search%")
                    ->orWhere('selected_files_for_scraping_view.scraper_created_at', 'LIKE', "%$search%");
            })->paginate(10);
            return $myFiles;
        }
        
        return SFFS::select('selected_files_for_scraping_view.*')
            ->join(
                'selected_files_for_scraping_view',
                'selected_files_for_scrapings.uuid',
                '=',
                'selected_files_for_scraping_view.uuid'
            )->paginate(10);
    }
}
