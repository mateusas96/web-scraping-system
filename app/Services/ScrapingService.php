<?php

namespace App\Services;
use App\Services\Contracts\ScrapingServiceInterface;
use App\Models\SelectedFilesForScraping as SFFS;
use App\Models\File;
use App\Models\ScrapingCategoryData AS SCD;
use App\Models\ScrapingCategoryDataHarvest AS SCDH;
use App\Models\ScrapingProductScrape AS SPS;
use App\Mail\VerifyEmail;
use Mail;
use Weidner\Goutte\GoutteFacade AS Goutte;

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
    public function scrape($uuid)
    {
        $config = null;
        $file_name = null;
        $scraper_name = null;
        $scrape_detailed_product_info = null;
        $selectedFilesData = null;
        $selectedFiles = null;

        $selectedFilesData = SFFS::findByUuid($uuid);

        $selectedFiles = File::select('file_path', 'file_name')
            ->whereRaw('FIND_IN_SET(id, ?)', $selectedFilesData['selected_files_id'])
            ->get();

        for($i = 0; $i < count($selectedFiles); $i++) {
            $file_name = explode('.', $selectedFiles[$i]['file_name'])[0];
            $scraper_name = $selectedFilesData['scraper_name'];
            $scrape_detailed_product_info = 
                $selectedFilesData['detailed_information_about_product'] == 1 ? 
                true : false;
            
            $config = json_decode(
                file_get_contents(
                    public_path() . $selectedFiles[$i]['file_path'] . $selectedFiles[$i]['file_name']
                )
            , true);

            // $this->categoryScrape($file_name, $config, $scraper_name);

            // foreach($this->main_category as $category) {
            //     $this->categoryDataScrape($file_name, $config, $scraper_name, $category);
            // }

            if ($scrape_detailed_product_info) {
                foreach($this->main_category as $category) {
                    $this->detailedProductDataScrape($file_name, $config, $scraper_name, $category);
                }
            }
        }

        $email = new VerifyEmail('success');
        Mail::to('matasxlx@gmail.com')->send($email);
    }

    /**
     * Harvest category
     * 
     * @param String scraping file name $file_name
     * @param Json scraper config $config
     * @param String scraper name $scraper_name
     */
    private function categoryScrape($file_name, $config, $scraper_name)
    {
        $crawler = null;
        $category_links_selector_type = null;
        $category_names_selector_type = null;
        $category_links_selector = null;
        $category_names_selector = null;
        $category_links_attribute = null;
        $category_names_attribute = null;
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

        $category_links_selector_type = $config[$file_name]['category_scrape']['category_links'][0];
        $category_names_selector_type = $config[$file_name]['category_scrape']['category_names'][0];
        $category_links_selector = $config[$file_name]['category_scrape']['category_links'][1];
        $category_names_selector = $config[$file_name]['category_scrape']['category_names'][1];
        $category_links_attribute = $config[$file_name]['category_scrape']['category_links'][2];
        $category_names_attribute = $config[$file_name]['category_scrape']['category_names'][2];

        $link_array = [
            0 => $config[$file_name]['category_scrape']['category_start_urls']['women_clothes'],
            1 => $config[$file_name]['category_scrape']['category_start_urls']['men_clothes'],
            2 => $config[$file_name]['category_scrape']['category_start_urls']['children_clothes'],
        ];

        for($i = 0; $i < count($link_array); $i++) {

            if ($link_array[$i][0] != null || $link_array[$i][0] != '') {

                for($j = 0; $j < count($link_array[$i]); $j++) {

                    $crawler = Goutte::request('GET', $link_array[$i][$j]);

                    if ($category_links_selector_type == 'xpath') {
                        $crawler->filterXpath($category_links_selector)->each(function ($node) use (&$category_harvest, &$i) {
                            $category_harvest[$i]['links'][] = $node->text();
                        });
                    } else {
                        $crawler->filter($category_links_selector)->each(function ($node) use (&$category_harvest, &$i, &$category_links_attribute) {
                            $category_harvest[$i]['links'][] = $node->extract([$category_links_attribute]);
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
                }
            }
        }

        for($i = 0; $i < count($category_harvest); $i++) { 

            if ($category_harvest[$i]['links'] != null || $category_harvest[$i]['links'] != '') {

                for($j = 0; $j < count($category_harvest[$i]['links']); $j++) {
                    SCD::create([
                        'scraper_name' => $scraper_name,
                        'category' => $this->main_category[$i],
                        'category_name' => $category_harvest[$i]['names'][$j],
                        'category_link' => $category_harvest[$i]['links'][$j],
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
     * @param String category $category
     */
    private function categoryDataScrape($file_name, $config, $scraper_name, $category)
    {
        $parsed_data_from_db = [];
        $parsed_data = [
            'product_name' => [],
            'product_link' => [],
            'normal_price' => [],
            'old_price' => [],
        ];
        $crawler = null;
        $data_parent_selector = null;
        $product_name_selector = null;
        $product_name_attribute = null;
        $product_link_selector = null;
        $product_link_attribute = null;
        $normal_price_selector = null;
        $normal_price_attribute = null;
        $old_price_selector = null;
        $old_price_attribute = null;
        $selector_type = null;

        $parsed_data_from_db = SCD::select(
            'category_link', 
            'category_name'
        )->where('category', $category)
        ->where('scraper_name', $scraper_name)->get();

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

            for($i = 0; $i < count($parsed_data_from_db); $i++) {

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
                        &$old_price_selector) {

                            $parsed_data['product_name'][] = $node->filterXpath($product_name_selector)->text();
                            $parsed_data['product_link'][] = $node->filterXpath($product_link_selector)->text();
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
                        &$old_price_attribute) {

                            $parsed_data['product_name'][] = $node->filter($product_name_selector)->extract([$product_name_attribute])[0];
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
                    
                    SCDH::create([
                        'scraper_name' => $scraper_name,
                        'category' => $category,
                        'category_name' => $parsed_data_from_db[$i]['category_name'],
                        'product_name' => $parsed_data['product_name'][$j],
                        'product_link' => $parsed_data['product_link'][$j],
                        'normal_price' => $parsed_data['normal_price'][$j],
                        'old_price' => $parsed_data['old_price'][$j],
                    ]);
                }

                $parsed_data = [
                    'product_name' => [],
                    'product_link' => [],
                    'normal_price' => [],
                    'old_price' => [],
                ]; 
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
     */
    private function detailedProductDataScrape($file_name, $config, $scraper_name, $category)
    {
        $parsed_data_from_db = [];
        $parsed_data = [
            'name' => [],
            'color' => [],
            'available_size' => [],
            'unavailable_size' => [],
        ];
        $crawler = null;
        $name_selector_type = null;
        $name_selector = null;
        $name_attribute = null;
        $color_selector_type = null;
        $color_selector = null;
        $color_attribute = null;
        $available_size_selector_type = null;
        $available_size_selector = null;
        $available_size_attribute = null;
        $unavailable_size_selector_type = null;
        $unavailable_size_selector = null;
        $unavailable_size_attribute = null;

        $parsed_data_from_db = SCDH::select(
            'product_link', 
            'category_name'
        )->where('category', $category)
        ->where('scraper_name', $scraper_name)->get();

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

                $name = null;
                $color = null;
                $available_size = null;
                $unavailable_size = null;

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
                $color = implode(', ', $parsed_data['color']);
                $available_size = implode(', ', $parsed_data['available_size']);
                $unavailable_size = implode(', ', $parsed_data['unavailable_size']);

                SPS::create([
                    'scraper_name' => $scraper_name,
                    'category' => $category,
                    'category_name' => $parsed_data_from_db[$i]['category_name'],
                    'name' => $name,
                    'color' => $color,
                    'available_size' => $available_size,
                    'unavailable_size' => $unavailable_size,
                ]);

                $parsed_data = [
                    'name' => [],
                    'color' => [],
                    'available_size' => [],
                    'unavailable_size' => [],
                ];
            }
        }
    }
}