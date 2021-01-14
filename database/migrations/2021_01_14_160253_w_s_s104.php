<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS104 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `selected_files_for_scrapings`
                ADD COLUMN `started_scraping_date` TIMESTAMP NULL AFTER `scraping_params`,
                ADD COLUMN `stopped_scraping_date` TIMESTAMP NULL AFTER `started_scraping_date`;
        ";

        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
