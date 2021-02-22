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
        'detailed_information_about_product',
        'schedule',
        'started_scraping_date',
        'stopped_scraping_date',
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
        'started_scraping_date' => 'datetime:Y-m-d H:i:s',
        'stopped_scraping_date' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * find data by uuid
     */
    public static function findByUuid($uuid) {
        $file = SelectedFilesForScraping::select('*')->where('uuid', $uuid)->first();

        if (isset($file)) {
            return $file;
        }

        return [
            'error' => true,
            'message' => 'Object not found'
        ];
    }

    /**
     * find data by uuid and active user id
     */
    public static function findByUuidAndActiveUserId($uuid, $active_user_id) {
        $file = SelectedFilesForScraping::select('*')->where('uuid', $uuid)->where('selected_by_user_id', $active_user_id)->first();

        if (isset($file)) {
            return $file;
        }

        return [
            'error' => true,
            'message' => 'Object not found'
        ];
    }

}
