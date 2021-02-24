<?php

namespace App\Services;
use App\Services\Contracts\SelectedFilesForScrapingServiceServiceInterface;
use App\Models\SelectedFilesForScraping as SFFS;
use DB;
use Carbon\Carbon;
use App\Models\File;
use App\Models\ScrapingCategoryData AS SCD;

class SelectedFilesForScrapingService implements SelectedFilesForScrapingServiceServiceInterface
{
    public function updateScraperStatus($uuid, $scraper_status, $system){
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

        if ($scraper_status == 'scraping_finished') {
            $sffs->status_id = $statusId;
            $sffs->stopped_scraping_date = Carbon::now();
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
    }
}