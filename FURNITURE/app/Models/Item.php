<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'name',
        'description',
        'category_ID',
        'FURNITURE_ID',
    ];
    public $timestamps = false;

}

