<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = 'userimage';
    protected $fillable = [
        'image_name',
        'image_size',
        'image_type',
        'image_path',
        'user_id'
    ];

    /**
     * Return user who created article, article owned by user
     */
    public function user(){
        return $this->belongsTo('App\User');
    }
}
