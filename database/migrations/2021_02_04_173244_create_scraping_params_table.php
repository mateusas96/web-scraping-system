<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrapingParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraping_params', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('scraper_name', 50);
            $table->string('root_category', 30);
            $table->string('subcategory', 150);
            $table->string('product_name', 150);
            $table->timestamps();
            $table->index([
                'scraper_name',
                'root_category',
                'subcategory',
                'product_name'
            ], 'scraper_category_name_index');
        });

        $sql = "
                CREATE TRIGGER `scraping_params_BINS` BEFORE INSERT ON `scraping_params` FOR EACH ROW
                BEGIN
                    IF NEW.uuid IS NULL THEN
                        SET NEW.uuid = UUID();
                    END IF;
                END;
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
        Schema::dropIfExists('scraping_params');
    }
}
