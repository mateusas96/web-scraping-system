<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ScrapingService;

class ScraperTest extends TestCase
{
    /**
     * Scraper service testing with existing data
     *
     * @return void
     */
    public function test_scraper_with_existing_scraper_uuid_and_scraper_creator_id()
    {
        $scraper = new ScrapingService();
        $scraperUuid = '65832202-92fd-11eb-9324-902b345ebf46';
        $scraperCreatorId = 1;

        /**
         * scraper should return value - true, 
         * because scraping should be successful with given existing data
         */
        $scraperResult = $scraper->scrape($scraperUuid, $scraperCreatorId);

        $this->assertEquals($scraperResult, true);
    }

    /**
     * Scraper service testing with not existing data
     *
     * @return void
     */
    public function test_scraper_with_not_existing_scraper_uuid_and_scraper_creator_id()
    {
        $scraper = new ScrapingService();
        $scraperUuid = '65832202-92fd';
        $scraperCreatorId = 0;

        /**
         * scraper should return value - false, 
         * because scraping should be unsuccessful with given not existing data
         */
        $scraperResult = $scraper->scrape($scraperUuid, $scraperCreatorId);

        $this->assertEquals($scraperResult, false);
    }
}
