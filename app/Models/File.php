<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'uploaded_by_user_id',
        'file_name',
        'mime_type',
        'file_size',
        'file_path',
        'uploaded_by_user_username',
        'version',
        'error_msg',
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

    /**
     * find data by uuid
     */
    public static function findByUuid($uuid) {
        $file = File::select('*')->where('uuid', $uuid)->first();

        if (isset($file)) {
            return $file;
        }

        return [
            'error' => true,
            'message' => 'Object not found'
        ];
    }
}
