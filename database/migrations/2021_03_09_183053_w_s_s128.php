<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WSS128 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
                ALTER TABLE `selected_files_for_scrapings` CHANGE  `stopped_scraping_date` `finished_scraping_date` TIMESTAMP NULL;

                DROP VIEW IF EXISTS `selected_files_for_scraping_view`;

                CREATE VIEW `selected_files_for_scraping_view` AS
                SELECT 
                    `sffs`.`id` AS `id`,
                    `sffs`.`uuid` AS `uuid`,
                    `sffs`.`scraper_name`,
                    `sffs`.`selected_by_user_id` AS `selected_by_user_id`,
                    (
                        SELECT
                            GROUP_CONCAT(`f`.`file_name` SEPARATOR ', ')
                        FROM `files` AS `f`
                        WHERE 
                            FIND_IN_SET(`f`.`id`, `sffs`.`selected_files_id`)
                    ) AS `selected_files`,
                    (
                        SELECT
                            GROUP_CONCAT(`f`.`error_msg` SEPARATOR ', ')
                        FROM `files` AS `f`
                        WHERE 
                            FIND_IN_SET(`f`.`id`, `sffs`.`selected_files_id`)
                    ) AS `selected_files_error_messages`,
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
                        `sffs`.`detailed_information_about_product` = 1,
                        'Yes',
                        'No'
                    ) AS `detailed_information_about_product`,
                    (
                        IFNULL(
                            (
                                SELECT
                                    GROUP_CONCAT(`sp`.`root_category`, ' - ', `sp`.`subcategory`, ' - ', `sp`.`product_name` SEPARATOR ',\n')
                                FROM `scraping_params` AS `sp`
                                WHERE
                                    `sp`.`scraper_name` = `sffs`.`scraper_name`
                            ),
                            'All'
                        )
                    ) AS `scraping_params`,
                    `sffs`.`schedule` AS `schedule`,
                    IF (
                        `sffs`.`scraper_stopped` = 1,
                        'Yes',
                        'No'
                    ) AS `scraper_stopped`,
                    `sffs`.`started_scraping_date` AS `started_scraping_date`,
                    `sffs`.`finished_scraping_date` AS `finished_scraping_date`,
                    `sffs`.`created_at` AS `scraper_created_at`
                FROM `selected_files_for_scrapings` AS `sffs`
                ORDER BY `sffs`.`created_at` DESC;
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
