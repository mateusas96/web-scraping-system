<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('file_size');
            $table->string('file_path');
            $table->timestamps();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users');
            $table->index(['uploaded_by_user_id', 'file_name']);
        });

        $sql = "
                CREATE TRIGGER `files_BINS` BEFORE INSERT ON `files` FOR EACH ROW
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
        Schema::dropIfExists('files');
    }
}
