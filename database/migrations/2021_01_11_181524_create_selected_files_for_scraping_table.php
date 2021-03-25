<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelectedFilesForScrapingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selected_files_for_scrapings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('scraper_name', 50);
            $table->unsignedBigInteger('selected_by_user_id');
            $table->json('selected_files_id');
            $table->unsignedBigInteger('status_id');
            $table->boolean('scrape_all')->default(0);
            $table->json('scraping_params');
            $table->timestamps();
            $table->foreign('selected_by_user_id')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('status');
            $table->index([
                'scraper_name',
                'selected_by_user_id',
            ], 'file_id_and_scraper_name_index');
        });

        $sql = "
                CREATE TRIGGER `selected_files_for_scrapings_BINS` BEFORE INSERT ON `selected_files_for_scrapings` FOR EACH ROW
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
        Schema::dropIfExists('selected_files_for_scraping');
    }
}
