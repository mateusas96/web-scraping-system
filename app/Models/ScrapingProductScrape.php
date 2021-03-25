<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingProductScrape extends Model
{
    protected $table = 'scraping_product_scrapes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'scdh_table_id',
        'user_id',
        'scraper_name',
        'category',
        'category_name',
        'name',
        'color',
        'available_size',
        'unavailable_size',
        'currency',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
