<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('code', 50);
            $table->timestamps();
        });

        $sql = "
                CREATE TRIGGER `status_BINS` BEFORE INSERT ON `status` FOR EACH ROW
                BEGIN
                    IF NEW.uuid IS NULL THEN
                        SET NEW.uuid = UUID();
                    END IF;
                END;
        ";

        DB::connection()->getPdo()->exec($sql);

        $sql = "
                INSERT INTO `status` (`code`, `created_at`, `updated_at`)
                VALUES
                    ('scraping_not_started', NOW(), NOW()),
                    ('scraping_initiated', NOW(), NOW()),
                    ('scraping_finished', NOW(), NOW()),
                    ('scraping_stopped_for_a_reason', NOW(), NOW());
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
        Schema::dropIfExists('status');
    }
}
