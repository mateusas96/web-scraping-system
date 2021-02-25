<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS127 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `files` 
                    ADD COLUMN `approvement_status_id` INT DEFAULT NULL AFTER `error_msg`,
                    ADD COLUMN `returned_for_fixing_message` TEXT DEFAULT NULL AFTER `approvement_status_id`,
                    ADD COLUMN `approvement_date` TIMESTAMP NULL DEFAULT NULL AFTER `returned_for_fixing_message`;

                INSERT INTO `status` (`code`, `created_at`, `updated_at`)
                VALUES
                    ('approvement_sent_for_approval', NOW(), NOW()),
                    ('approvement_approved', NOW(), NOW()),
                    ('approvement_returned_for_fixing', NOW(), NOW());
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
