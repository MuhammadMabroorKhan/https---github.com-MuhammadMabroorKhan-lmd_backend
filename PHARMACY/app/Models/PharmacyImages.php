<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyImages extends Model
{
    protected $table = 'pharmacy_images';
    protected $fillable = [
        'pharmacy_id',
        'image_path',
        'description'
    ];
    public $timestamps = false;
}
