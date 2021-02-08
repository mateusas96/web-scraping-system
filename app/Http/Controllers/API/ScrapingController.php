<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Scraping;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\File;
use App\Models\ScrapingCategoryData AS SCD;
use Illuminate\Http\Request;
use App\Jobs\ScrapeData;

class ScrapingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Scraping  $scraping
     * @return \Illuminate\Http\Response
     */
    public function show(Scraping $scraping)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Scraping  $scraping
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Scraping $scraping)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Scraping  $scraping
     * @return \Illuminate\Http\Response
     */
    public function destroy(Scraping $scraping)
    {
        //
    }

    public function startScrapingData($uuid) {
        // $selectedFilesId = SFFS::findByUuid($uuid)['selected_files_id'];

        // $selectedFiles = File::select('file_path', 'file_name')
        //     ->whereRaw('FIND_IN_SET(id, ?)', $selectedFilesId)
        //     ->get();

        // $json = json_decode(file_get_contents($selectedFiles[0]['file_path'] . $selectedFiles[0]['file_name']), true);

        // return $selectedFiles[0]['file_name'];
        // return SCD::select('category_link')->where('category', 'Women')->where('scraper_name', 'apc json test')->get();
        $scraper = new ScrapeData($uuid, auth()->user()->id);
        dispatch($scraper);
    }
}
