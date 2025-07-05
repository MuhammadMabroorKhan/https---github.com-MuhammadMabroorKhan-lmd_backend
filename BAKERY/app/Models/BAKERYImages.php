<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BAKERYImages extends Model
{
    protected $table = 'bakery_images';
    protected $fillable = [
        'bakery_id',
        'image_path',
        'description'
    ];
    public $timestamps = false;
}
