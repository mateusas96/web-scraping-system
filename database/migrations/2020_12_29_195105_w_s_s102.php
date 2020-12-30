<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS102 extends Migration
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
                ADD COLUMN `version` INT DEFAULT(1) AFTER `uploaded_by_user_username`;

                CREATE TRIGGER `files_version_BINS` BEFORE INSERT ON `files` FOR EACH ROW
                BEGIN
                    IF NEW.version IS NULL THEN
                        SET NEW.version = 1;
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
        //
    }
}
