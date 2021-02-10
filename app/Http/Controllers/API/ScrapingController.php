<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Scraping;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\File;
use App\Models\ScrapingCategoryDataHarvest AS SCDH;
use App\Models\ScrapingProductScrape AS SPS;
use Illuminate\Http\Request;
use App\Jobs\ScrapeData;
use App\Services\ScrapingService;

class ScrapingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $scraper_name = $request->get('scraper_name');

        $data = SCDH::select(
            'scraping_category_data_harvests.id',
            'scraping_category_data_harvests.scraped_detail_info',
            'scraping_category_data_harvests.category',
            'scraping_category_data_harvests.category_name',
            'scraping_category_data_harvests.product_name',
            'scraping_category_data_harvests.product_link',
            'scraping_category_data_harvests.normal_price',
            'scraping_category_data_harvests.old_price',
            'scraping_category_data_harvests.currency',
            'scraping_category_data_harvests.created_at',
            'scraping_product_scrapes.color',
            'scraping_product_scrapes.available_size',
            'scraping_product_scrapes.unavailable_size'
        )->leftJoin(
            'scraping_product_scrapes',
            'scraping_product_scrapes.scdh_table_id',
            '=',
            'scraping_category_data_harvests.id'
        )->where(
            'scraping_category_data_harvests.scraper_name', '=', $scraper_name
        )->where(
            'scraping_category_data_harvests.user_id', '=', auth()->user()->id
        )->orderBy('scraping_category_data_harvests.created_at', 'DESC')->paginate(10);

        return $data;       
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

    public function runScraperOnce($uuid) {
        $scraping_service = new ScrapingService();
        $response = $scraping_service->scrape($uuid, auth()->user()->id);

        return response()->json([
            'finished_scraping' => true,
            'message' => 'Scraping was successful',
        ], 200);
        // $scraper = new ScrapeData($uuid, auth()->user()->id);
        // dispatch($scraper);
    }
}
