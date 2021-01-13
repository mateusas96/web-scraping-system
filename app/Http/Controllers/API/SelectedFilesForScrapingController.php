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
        return SFFS::select('*')->get();
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
        $scraperParams = $request->get('scraper_params');
        $selectedFiles = $request->get('selected_files');
        $scrapeAll = $request->get('scrape_all');

        $statusId = collect(
            DB::select('SELECT get_status_id_by_code("scraping_not_started") as statusId')
        )->first()->statusId;

        SFFS::create([
            'scraper_name' => $scraperName,
            'selected_by_user_id' => auth()->user()->id,
            'selected_files_id' => json_encode($selectedFiles),
            'status_id' => $statusId,
            'scrape_all' => $scrapeAll === null ? false : $scrapeAll,
            'scraping_params' => json_encode($scraperParams),
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
}
