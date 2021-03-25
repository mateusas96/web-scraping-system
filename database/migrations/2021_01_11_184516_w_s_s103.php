<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS103 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                DROP FUNCTION IF EXISTS `get_status_id_by_code`;
                
                CREATE FUNCTION `get_status_id_by_code`(status_code VARCHAR(50)) 
                RETURNS INT(11) 
                BEGIN
                    DECLARE
                        status_id INT(11) DEFAULT NULL;
                    SELECT
                        `s`.`id`
                    FROM
                        `status` AS `s`
                    WHERE
                        `s`.`code` = status_code
                    INTO status_id; 
                    
                    RETURN status_id;
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
