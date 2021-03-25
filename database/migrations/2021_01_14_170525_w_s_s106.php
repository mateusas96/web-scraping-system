<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS106 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `selected_files_for_scrapings`
                MODIFY COLUMN `selected_files_id` TEXT NOT NULL,
                MODIFY COLUMN `scraping_params` TEXT NULL;
        
                DROP PROCEDURE IF EXISTS `get_my_scraping_files`;

                CREATE PROCEDURE `get_my_scraping_files`(user_id INT, offset INT)
                BEGIN
                    SELECT 
                        `sffs`.`uuid`,
                        `sffs`.`scraper_name`,
                        (
                            SELECT
                                GROUP_CONCAT(`f`.`file_name` SEPARATOR ', ')
                            FROM `files` AS `f`
                            WHERE 
                                FIND_IN_SET(`f`.`id`, `sffs`.`selected_files_id`)
                        ) AS `selected_files`,
                        (
                            SELECT
                                `s`.`code`
                            FROM `status` AS `s`
                            WHERE
                                `s`.`id` = `sffs`.`status_id`
                        ) AS `scraping_status`,
                        IF (
                            `sffs`.`scrape_all` = 1,
                            'Yes',
                            'No'
                        ) AS `scrape_all`,
                        IF (
                            `sffs`.`scraping_params` IS NULL,
                            NULL,
                            `sffs`.`scraping_params`
                        ) AS `scraping_params`,
                        `sffs`.`started_scraping_date` AS `started_scraping_date`,
                        `sffs`.`stopped_scraping_date` AS `stopped_scraping_date`,
                        `sffs`.`created_at` AS `scraper_created_at`
                    FROM `selected_files_for_scrapings` AS `sffs`
                    INNER JOIN `users` AS `u` ON
                        `sffs`.`selected_by_user_id` = `u`.`id` AND
                        `u`.`id` = user_id
                    LIMIT 10 OFFSET offset;
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
