<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ScrapingService;

class ScrapeData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sffs_uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sffs_uuid)
    {
        $this->sffs_uuid = $sffs_uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scrapingService = new ScrapingService();
        $scrapingService->scrape($this->sffs_uuid);
    }
}
