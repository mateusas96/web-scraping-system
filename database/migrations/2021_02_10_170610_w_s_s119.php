<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS119 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `scraping_category_datas` ADD COLUMN `currency` VARCHAR(50) AFTER `category_link`;
                ALTER TABLE `scraping_category_data_harvests` ADD COLUMN `currency` VARCHAR(50) AFTER `old_price`;
                ALTER TABLE `scraping_product_scrapes` ADD COLUMN `currency` VARCHAR(50) AFTER `unavailable_size`;
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
