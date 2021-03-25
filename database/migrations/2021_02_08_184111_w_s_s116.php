<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS116 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `scraping_params` ADD COLUMN `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `uuid`;
                ALTER TABLE `scraping_category_datas` ADD COLUMN `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;
                ALTER TABLE `scraping_category_data_harvests` ADD COLUMN `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;
                ALTER TABLE `scraping_product_scrapes` ADD COLUMN `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;
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
