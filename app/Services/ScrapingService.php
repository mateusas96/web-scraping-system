<?php

namespace App\Services;
use App\Services\Contracts\ScrapingServiceInterface;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\File;
use App\Models\ScrapingCategoryData AS SCD;
use App\Models\ScrapingCategoryDataHarvest AS SCDH;
use App\Models\ScrapingProductScrape AS SPS;
use App\Models\ScrapingParam as SP;
use App\Mail\VerifyEmail;
use Mail;
use Weidner\Goutte\GoutteFacade AS Goutte;
use App\Services\SelectedFilesForScrapingService as SFFSS;

class ScrapingService implements ScrapingServiceInterface
{
    private $main_category = [
        0 => 'Women',
        1 => 'Men',
        2 => 'Children',
    ];

    public function make(array $request)
    {
        // TODO: Implement make() method.
        // put all the logic in this class
    }

    /**
     * Initiate scraping
     * 
     * @param String selected file for scraping $uuid  
     */
    public function scrape($uuid, $user_id)
    {
        set_time_limit(0);
        $config = $file_name = $scraper_name = $scrape_detailed_product_info = null;
        $selected_files_data = $selected_files = $scrape_all = $filter_params = null;

        $selected_files_data = SFFS::findByUuidAndActiveUserId($uuid, $user_id);

        $selected_files = File::select('file_path', 'file_name')
            ->whereRaw('FIND_IN_SET(id, ?)', $selected_files_data['selected_files_id'])
            ->get();

        $scraper_name = $selected_files_data['scraper_name'];
        $scrape_detailed_product_info = 
            $selected_files_data['detailed_information_about_product'] == 1 ? 
            true : false;
        $scrape_all = $selected_files_data['scrape_all'] == 1 ?
            true : false;

        for($i = 0; $i < count($selected_files); $i++) {

            $file_name = explode('.', $selected_files[$i]['file_name'])[0];
            
            $config = json_decode(
                file_get_contents(
                    public_path() . $selected_files[$i]['file_path'] . $selected_files[$i]['file_name']
                )
            , true);
            
            $filter_params = SP::selectRaw(
                '
                IF (
                    root_category = \'Women\',
                    0,
                    IF (
                        root_category = \'Men\',
                        1,
                        IF (
                            root_category = \'Children\',
                            2,
                            NULL
                        )
                    )
                ) AS `root_category`,
                `subcategory`,
                `product_name`
                '
            )->where(
                'scraper_name', '=', $scraper_name
            )->where(
                'user_id', '=', $user_id
            )->get();

            $this->categoryScrape($file_name, $config, $scraper_name, $scrape_all, $filter_params, $user_id);

            foreach($this->main_category as $index => $category) {
                $this->categoryDataScrape($file_name, $config, $scraper_name, $index, $category, $scrape_all, $filter_params, $user_id);
            }

            if ($scrape_detailed_product_info) {
                foreach($this->main_category as $category) {
                    $this->detailedProductDataScrape($file_name, $config, $scraper_name, $category, $user_id);
                }

                SCDH::where('scraper_name', '=', $scraper_name)
                    ->where('user_id', '=', $user_id)
                    ->where('scraped_detail_info', '=', 0)
                    ->update(['scraped_detail_info' => 1]);
            }
        }
        $sffsService = new SFFSS();
        $sffsService->updateScraperStatus($uuid, 'scraping_finished', true);

        SCD::where('scraper_name', $scraper_name)->where('user_id', '=', $user_id)->delete();

        return true;
    }

    /**
     * Harvest category
     * 
     * @param String scraping file name $file_name
     * @param Json scraper config $config
     * @param String scraper name $scraper_name
     * @param Boolean scrape all $scrape_all
     * @param Array data filtering params $filter_params
     * @param Int active user id $user_id
     */
    private function categoryScrape($file_name, $config, $scraper_name, $scrape_all, $filter_params, $user_id)
    {
        $crawler = $category_links_selector_type = $category_names_selector_type = $category_links_selector = null;
        $category_names_selector = $category_links_attribute = $category_names_attribute = $domain_url = null;
        $category_harvest = [
            0 => [
                'links' => [],
                'names' => [],
            ],
            1 => [
                'links' => [],
                'names' => [],
            ],
            2 => [
                'links' => [],
                'names' => [],
            ],
        ];

        $domain_url = $config[$file_name]['category_scrape']['allowed_domains'][0];

        $link_array = [
            0 => $config[$file_name]['category_scrape']['category_start_urls']['women_clothes'],
            1 => $config[$file_name]['category_scrape']['category_start_urls']['men_clothes'],
            2 => $config[$file_name]['category_scrape']['category_start_urls']['children_clothes'],
        ];

        for($i = 0; $i < count($link_array); $i++) {
            
            if (!$scrape_all) {

                for($j = 0; $j < count($filter_params); $j++) {
                    $root_category_exists = false;

                    if ($i == $filter_params[$j]['root_category']) {
                        $root_category_exists = true;
                        break;
                    }
                }
    
                if (!$root_category_exists) {
                    continue;
                }
            }

            if ($link_array[$i][0] != null || $link_array[$i][0] != '') {

                $temp_category = null;

                if ($i == 0) {
                    $temp_category = 'women_clothes';
                } else if ($i == 1) {
                    $temp_category = 'men_clothes';
                } else if ($i == 2) {
                    $temp_category = 'children_clothes';
                }

                $category_links_selector_type = $config[$file_name]['category_scrape']['category_links'][$temp_category][0];
                $category_names_selector_type = $config[$file_name]['category_scrape']['category_names'][$temp_category][0];
                $category_links_selector = $config[$file_name]['category_scrape']['category_links'][$temp_category][1];
                $category_names_selector = $config[$file_name]['category_scrape']['category_names'][$temp_category][1];
                $category_links_attribute = $config[$file_name]['category_scrape']['category_links'][$temp_category][2];
                $category_names_attribute = $config[$file_name]['category_scrape']['category_names'][$temp_category][2];

                for($j = 0; $j < count($link_array[$i]); $j++) {

                    $crawler = Goutte::request('GET', $link_array[$i][$j]);

                    if ($category_links_selector_type == 'xpath') {
                        $crawler->filterXpath($category_links_selector)->each(function ($node) use (&$category_harvest, &$i, &$domain_url) {
                            if (stristr($node->text(), $domain_url)) {
                                $category_harvest[$i]['links'][] = $node->text();
                            } else {
                                $category_harvest[$i]['links'][] = $domain_url . $node->text();
                            }
                        });
                    } else {
                        $crawler->filter($category_links_selector)->each(function ($node) use (&$category_harvest, &$i, &$category_links_attribute, &$domain_url) {
                            if (stristr($node->extract([$category_links_attribute]), $domain_url)) {
                                $category_harvest[$i]['links'][] = $node->extract([$category_links_attribute]);
                            } else {
                                $category_harvest[$i]['links'][] = $domain_url . $node->extract([$category_links_attribute]);
                            }
                        });
                    }

                    if ($category_names_selector_type == 'xpath') {
                        $crawler->filterXpath($category_names_selector)->each(function ($node) use (&$category_harvest, &$i) {
                            $category_harvest[$i]['names'][] = $node->text();
                        });
                    } else {
                        $crawler->filter($category_names_selector)->each(function ($node) use (&$category_harvest, &$i, &$category_names_attribute) {
                            $category_harvest[$i]['names'][] = $node->extract([$category_names_attribute]);
                        });
                    }

                    $crawler = null;
                }
            }
        }
  
        for($i = 0; $i < count($category_harvest); $i++) {

            if ($category_harvest[$i]['links'] != null || $category_harvest[$i]['links'] != '') {

                for($j = 0; $j < count($category_harvest[$i]['links']); $j++) {

                    if (!$scrape_all) {
                        for($k = 0; $k < count($filter_params); $k++) {
                            $subcategory_exists = false;

                            if (
                                stristr($category_harvest[$i]['names'][$j], $filter_params[$k]['subcategory']) &&
                                $i == $filter_params[$k]['root_category']
                            ) {
                                $subcategory_exists = true;
                                break;
                            }
                        }
            
                        if (!$subcategory_exists) {
                            continue;
                        }
                    }

                    SCD::create([
                        'user_id' => $user_id,
                        'scraper_name' => $scraper_name,
                        'category' => $this->main_category[$i],
                        'category_name' => $category_harvest[$i]['names'][$j],
                        'category_link' => $category_harvest[$i]['links'][$j],
                        'currency' => $config[$file_name]['currency'],
                    ]);
                }
            }
        }
    }

    /**
     * Harvest category product data
     * 
     * @param String scraping file name $file_name
     * @param Json scraper config $config
     * @param String scraper name $scraper_name
     * @param Int category index $category_index
     * @param String category $category
     * @param Boolean scrape all $scrape_all
     * @param Array data filtering params $filter_params
     * @param Int active user id $user_id
     */
    private function categoryDataScrape($file_name, $config, $scraper_name, $category_index, $category, $scrape_all, $filter_params, $user_id)
    {
        $parsed_data_from_db = [];
        $parsed_data = [
            'product_name' => [],
            'product_link' => [],
            'normal_price' => [],
            'old_price' => [],
        ];
        $crawler = $data_parent_selector = $product_name_selector = $product_name_attribute = null;
        $product_link_selector = $product_link_attribute = $normal_price_selector = $normal_price_attribute = null;
        $old_price_selector = $old_price_attribute = $selector_type = $pagination_selector_type = null;
        $pagination_selector = $pagination_selector_attribute = $pagination = $domain_url = $pagination_method = null;

        $parsed_data_from_db = SCD::select(
            'category_link', 
            'category_name'
        )->where('category', '=', $category)
        ->where('scraper_name', '=', $scraper_name)
        ->where('user_id', '=', $user_id)->get();

        if (count($parsed_data_from_db) > 0) {

            $data_parent_selector = $config[$file_name]['category_scrape']['category_harvest_data']['data_parent'][0];

            $product_name_selector = $config[$file_name]['category_scrape']['category_harvest_data']['product_name'][0];
            $product_name_attribute = $config[$file_name]['category_scrape']['category_harvest_data']['product_name'][1];

            $product_link_selector = $config[$file_name]['category_scrape']['category_harvest_data']['product_link'][0];
            $product_link_attribute = $config[$file_name]['category_scrape']['category_harvest_data']['product_link'][1];

            $normal_price_selector = $config[$file_name]['category_scrape']['category_harvest_data']['normal_price'][0];
            $normal_price_attribute = $config[$file_name]['category_scrape']['category_harvest_data']['normal_price'][1];

            $old_price_selector = $config[$file_name]['category_scrape']['category_harvest_data']['old_price'][0];
            $old_price_attribute = $config[$file_name]['category_scrape']['category_harvest_data']['old_price'][1];

            $selector_type = $config[$file_name]['category_scrape']['category_harvest_data']['all_selectors_type'];

            $pagination_method = $config[$file_name]['category_scrape']['category_pagination'][0];

            $pagination_selector_type = $config[$file_name]['category_scrape']['category_pagination'][1];

            $domain_url = $config[$file_name]['category_scrape']['allowed_domains'][0];

            if ($pagination_method == 'multi-load' && ($pagination_selector_type != '' || $pagination_selector_type != null)) {

                $pagination_selector = $config[$file_name]['category_scrape']['category_pagination'][2];
                $pagination_selector_attribute = $config[$file_name]['category_scrape']['category_pagination'][3];
                $pagination = $config[$file_name]['category_scrape']['category_pagination'][4];

                $temp_parsed_data_count = count($parsed_data_from_db);

                for($i = 0; $i < $temp_parsed_data_count; $i++) {

                    $crawler = Goutte::request('GET', $parsed_data_from_db[$i]['category_link']);

                    $temp_page_size = null;

                    if ($pagination_selector_type == 'xpath') {
                        $crawler->filterXpath($pagination_selector)->each(function ($node) use (&$i, &$temp_page_size) {
                            $temp_page_size = $node->text();
                        });
                    } else {
                        $crawler->filter($pagination_selector)->each(function ($node) use (&$i, &$pagination_selector_attribute, &$temp_page_size) {
                            $temp_page_size = $node->extract([$pagination_selector_attribute]);
                        });
                    }

                    $page_size = $temp_page_size[0];

                    for(
                        $j = $config[$file_name]['category_scrape']['pagination_parameters']['start_index']; 
                        $j <= ($page_size + $config[$file_name]['category_scrape']['pagination_parameters']['last_page']);
                        $j += $config[$file_name]['category_scrape']['pagination_parameters']['step_size']
                    ) {
                        $parsed_data_from_db[] = [
                            'category_link' => $parsed_data_from_db[$i]['category_link'] . $pagination . $j,
                            'category_name' => $parsed_data_from_db[$i]['category_name']
                        ];
                    }
                }
            } else if ($pagination_method == 'one-load') {
                $pagination = $config[$file_name]['category_scrape']['category_pagination'][4];

                for($i = 0; $i < count($parsed_data_from_db); $i++) {
                    $parsed_data_from_db[$i]['category_link'] .= $pagination;
                }
            }

            for($i = 0; $i < count($parsed_data_from_db); $i++) {
                $crawler = null;

                $crawler = Goutte::request('GET', $parsed_data_from_db[$i]['category_link']);

                if ($selector_type == 'xpath') {
                    $crawler->filterXpath($data_parent_selector)->each(function ($node) 
                        use (
                        &$data_parent_selector, 
                        &$scraper_name, 
                        &$category, 
                        &$parsed_data_from_db, 
                        &$parsed_data,
                        &$i,
                        &$product_name_selector,
                        &$product_link_selector,
                        &$normal_price_selector,
                        &$old_price_selector,
                        &$domain_url) {

                            $parsed_data['product_name'][] = $node->filterXpath($product_name_selector)->text();

                            if (stristr($node->filterXpath($product_link_selector)->text(), $domain_url)) {
                                $parsed_data['product_link'][] = $node->filterXpath($product_link_selector)->text();
                            } else {
                                $parsed_data['product_link'][] = $domain_url . $node->filterXpath($product_link_selector)->text();
                            }

                            $parsed_data['normal_price'][] = 
                                $node->filterXpath($normal_price_selector)->count() > 0 ? 
                                $node->filterXpath($normal_price_selector)->text() : null;

                            $parsed_data['old_price'][] = 
                                $node->filterXpath($old_price_selector)->count() > 0 ?
                                $node->filterXpath($old_price_selector)->text() : null;

                    });
                } else {
                    $crawler->filter($data_parent_selector)->each(function ($node) 
                        use (
                        &$data_parent_selector, 
                        &$scraper_name, 
                        &$category, 
                        &$parsed_data_from_db, 
                        &$parsed_data,
                        &$i,
                        &$product_name_selector,
                        &$product_name_attribute,
                        &$product_link_selector,
                        &$product_link_attribute,
                        &$normal_price_selector,
                        &$normal_price_attribute,
                        &$old_price_selector,
                        &$old_price_attribute,
                        &$domain_url) {

                            $parsed_data['product_name'][] = $node->filter($product_name_selector)->extract([$product_name_attribute])[0];

                            if (stristr($node->filter($product_link_selector)->extract([$product_link_attribute])[0], $domain_url)) {
                                $parsed_data['product_link'][] = $node->filter($product_link_selector)->extract([$product_link_attribute])[0];
                            } else {
                                $parsed_data['product_link'][] = $domain_url . $node->filter($product_link_selector)->extract([$product_link_attribute])[0];
                            }

                            $parsed_data['product_link'][] = $node->filter($product_link_selector)->extract([$product_link_attribute])[0];
                            $parsed_data['normal_price'][] = 
                                (
                                    $node->filter($normal_price_selector)->extract([$normal_price_attribute]) == null ||
                                    $node->filter($normal_price_selector)->extract([$normal_price_attribute]) == ''
                                ) ? null : $node->filter($normal_price_selector)->extract([$normal_price_attribute])[0];

                            $parsed_data['old_price'][] = 
                                (
                                    $node->filter($old_price_selector)->extract([$old_price_attribute]) == null ||
                                    $node->filter($old_price_selector)->extract([$old_price_attribute]) == ''
                                ) ? null : $node->filter($old_price_selector)->extract([$old_price_attribute])[0];

                    });
                }

                for($j = 0; $j < count($parsed_data['product_name']); $j++) {

                    if (!$scrape_all) {
                        for($k = 0; $k < count($filter_params); $k++) {
                            $product_name_exists = false;

                            if (
                                stristr($parsed_data['product_name'][$j], $filter_params[$k]['product_name']) &&
                                $category_index == $filter_params[$k]['root_category']
                            ) {
                                $product_name_exists = true;
                                break;
                            }
                        }
            
                        if (!$product_name_exists) {
                            continue;
                        }
                    }
                    
                    SCDH::create([
                        'user_id' => $user_id,
                        'scraper_name' => $scraper_name,
                        'category' => $category,
                        'category_name' => $parsed_data_from_db[$i]['category_name'],
                        'product_name' => $parsed_data['product_name'][$j],
                        'product_link' => $parsed_data['product_link'][$j],
                        'normal_price' => $parsed_data['normal_price'][$j],
                        'old_price' => $parsed_data['old_price'][$j],
                        'currency' => $config[$file_name]['currency'],
                    ]);
                }

                $parsed_data = [
                    'product_name' => [],
                    'product_link' => [],
                    'normal_price' => [],
                    'old_price' => [],
                ];

                $crawler = null;
            }
        }
    }

    /**
     * Harvest product detailed data
     * 
     * @param String scraping file name $file_name
     * @param Json scraper config $config
     * @param String scraper name $scraper_name
     * @param String category $category
     * @param Int active user id $user_id
     */
    private function detailedProductDataScrape($file_name, $config, $scraper_name, $category, $user_id)
    {
        $parsed_data_from_db = [];
        $parsed_data = [
            'name' => [],
            'color' => [],
            'available_size' => [],
            'unavailable_size' => [],
        ];
        $crawler = $name_selector_type = $name_selector = $name_attribute = $color_selector_type = null;
        $color_selector = $color_attribute = $available_size_selector_type = $available_size_selector = null;
        $available_size_attribute = $unavailable_size_selector_type = $unavailable_size_selector = $unavailable_size_attribute = null;

        $parsed_data_from_db = SCDH::select(
            'id',
            'product_link', 
            'category_name'
        )->where('category', '=', $category)
        ->where('scraper_name', '=', $scraper_name)
        ->where('user_id', '=', $user_id)
        ->where('scraped_detail_info', '=', 0)->get();

        if (count($parsed_data_from_db) > 0) {

            $name_selector_type = $config[$file_name]['product_scrape']['name'][0];
            $name_selector = $config[$file_name]['product_scrape']['name'][1];
            $name_attribute = $config[$file_name]['product_scrape']['name'][2];

            $color_selector_type = $config[$file_name]['product_scrape']['color'][0];
            $color_selector = $config[$file_name]['product_scrape']['color'][1];
            $color_attribute = $config[$file_name]['product_scrape']['color'][2];

            $available_size_selector_type = $config[$file_name]['product_scrape']['available_size'][0];
            $available_size_selector = $config[$file_name]['product_scrape']['available_size'][1];
            $available_size_attribute = $config[$file_name]['product_scrape']['available_size'][2];

            $unavailable_size_selector_type = $config[$file_name]['product_scrape']['unavailable_size'][0];
            $unavailable_size_selector = $config[$file_name]['product_scrape']['unavailable_size'][1];
            $unavailable_size_attribute = $config[$file_name]['product_scrape']['unavailable_size'][2];

            for($i = 0; $i < count($parsed_data_from_db); $i++) {

                $name = $scdh_table_id = $color = $available_size = $unavailable_size = null;

                $crawler = Goutte::request('GET', $parsed_data_from_db[$i]['product_link']);

                if ($name_selector_type == 'xpath') {
                    $crawler->filterXpath($name_selector)->each(function ($node) use (&$parsed_data) {
                        $parsed_data['name'][] = 
                            $node->count() > 0 ?
                            $node->text() : null;
                    });
                } else {
                    $crawler->filter($name_selector)->each(function ($node) use (&$parsed_data, &$name_attribute) {
                        $parsed_data['name'][] = 
                            $node->count() > 0 ?
                            $node->extract([$name_attribute])[0] : null;
                    });
                }

                if ($color_selector_type == 'xpath') {
                    $crawler->filterXpath($color_selector)->each(function ($node) use (&$parsed_data) {
                        $parsed_data['color'][] = 
                            $node->count() > 0 ?
                            $node->text() : null;
                    });
                } else {
                    $crawler->filter($color_selector)->each(function ($node) use (&$parsed_data, &$color_attribute) {
                        $parsed_data['color'][] = 
                            $node->count() > 0 ?
                            $node->extract([$color_attribute])[0] : null;
                    });
                }

                if ($available_size_selector_type == 'xpath') {
                    $crawler->filterXpath($available_size_selector)->each(function ($node) use (&$parsed_data) {
                        $parsed_data['available_size'][] = 
                            $node->count() > 0 ?
                            $node->text() : null;
                    });
                } else {
                    $crawler->filter($available_size_selector)->each(function ($node) use (&$parsed_data, &$available_size_attribute) {
                        $parsed_data['available_size'][] = 
                            $node->count() > 0 ?
                            $node->extract([$available_size_attribute])[0] : null;
                    });
                }

                if ($unavailable_size_selector_type == 'xpath') {
                    $crawler->filterXpath($unavailable_size_selector)->each(function ($node) use (&$parsed_data) {
                        $parsed_data['unavailable_size'][] = 
                            $node->count() > 0 ?
                            $node->text() : null;
                    });
                } else {
                    $crawler->filter($unavailable_size_selector)->each(function ($node) use (&$parsed_data, &$unavailable_size_attribute) {
                        $parsed_data['unavailable_size'][] = 
                            $node->count() > 0 ?
                            $node->extract([$unavailable_size_attribute])[0] : null;
                    });
                }

                $name = $parsed_data['name'][0];
                $scdh_table_id = $parsed_data_from_db[$i]['id'];
                $color = implode(', ', $parsed_data['color']);
                $available_size = implode(', ', $parsed_data['available_size']);
                $unavailable_size = implode(', ', $parsed_data['unavailable_size']);

                SPS::create([
                    'user_id' => $user_id,
                    'scdh_table_id' => $scdh_table_id,
                    'scraper_name' => $scraper_name,
                    'category' => $category,
                    'category_name' => $parsed_data_from_db[$i]['category_name'],
                    'name' => $name,
                    'color' => $color,
                    'available_size' => $available_size,
                    'unavailable_size' => $unavailable_size,
                    'currency' => $config[$file_name]['currency'],
                ]);

                $parsed_data = [
                    'scdh_table_id' => [],
                    'name' => [],
                    'color' => [],
                    'available_size' => [],
                    'unavailable_size' => [],
                ];
                $crawler = null;
            }
        }
    }
}