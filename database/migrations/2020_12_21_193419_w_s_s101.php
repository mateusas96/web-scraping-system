<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS101 extends Migration
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
                ADD COLUMN `uploaded_by_user_username` VARCHAR(256) DEFAULT(NULL) AFTER `uploaded_by_user_id`;

                ALTER TABLE `users`
                ADD COLUMN `username` VARCHAR(256) DEFAULT(NULL) AFTER `short_name`;
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
