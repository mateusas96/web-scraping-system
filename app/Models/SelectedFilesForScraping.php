<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectedFilesForScraping extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'scraper_name',
        'selected_by_user_id',
        'selected_files_id',
        'status_id',
        'scrape_all',
        'scraping_params',
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
