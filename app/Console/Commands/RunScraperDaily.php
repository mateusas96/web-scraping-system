<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Jobs\ScrapeData;
use App\Services\SelectedFilesForScrapingService as SFFSS;

class RunScraperDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-scraper:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs scrapers those are selected to run daily';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = SFFS::select(
            'selected_files_for_scrapings.uuid',
            'selected_by_user_id'
        )->join(
            'status', 'status.id', 'selected_files_for_scrapings.status_id'
        )->where(
            'schedule', 'daily'
        )->whereRaw(
            '
            (
                status.code = "scraping_not_started" OR
                status.code = "scraping_finished"
            )
            '
        )->where(
            'scraper_stopped',
            0
        )->get();

        $sffsService = new SFFSS();

        foreach($data as $value) {
            $sffsService->updateScraperStatus($value['uuid'], 'scraping_initiated', true);

            $scraper = new ScrapeData($value['uuid'], $value['selected_by_user_id']);
            dispatch($scraper);
        }
    }
}
