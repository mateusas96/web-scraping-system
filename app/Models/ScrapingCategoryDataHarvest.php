<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingCategoryDataHarvest extends Model
{
    protected $table = 'scraping_category_data_harvests';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'scraper_name',
        'category',
        'category_name',
        'product_name',
        'product_link',
        'normal_price',
        'old_price',
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
