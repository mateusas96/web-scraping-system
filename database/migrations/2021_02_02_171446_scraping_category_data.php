<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ScrapingCategoryData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraping_category_datas', function (Blueprint $table) {
            $table->id();
            $table->string('scraper_name', 50);
            $table->string('category', 100);
            $table->string('category_name', 100);
            $table->string('category_link', 1000);
            $table->timestamps();
            $table->index([
                'scraper_name',
                'category',
                'category_name'
            ], 'scraper_category_name_index');
        });

        Schema::create('scraping_category_data_harvests', function (Blueprint $table) {
            $table->id();
            $table->string('scraper_name', 50);
            $table->string('category', 100);
            $table->string('category_name', 100);
            $table->string('product_name', 100);
            $table->string('product_link', 1000);
            $table->string('normal_price', 10)->nullable();
            $table->string('old_price', 10)->nullable();
            $table->timestamps();
            $table->index([
                'scraper_name',
                'product_name',
                'category',
                'category_name'
            ], 'name_category_link_index');
        });

        Schema::create('scraping_product_scrapes', function (Blueprint $table) {
            $table->id();
            $table->string('scraper_name', 50);
            $table->string('category', 100);
            $table->string('category_name', 100);
            $table->string('name', 100);
            $table->string('color', 300)->nullable();
            $table->string('available_size', 300)->nullable();
            $table->string('unavailable_size', 300)->nullable();
            $table->timestamps();
            $table->index([
                'scraper_name',
                'name',
                'category',
                'category_name'
            ], 'scraper_category_name_index');
        });
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
