<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemDetail extends Model
{
    protected $table = 'itemdetails';

    protected $fillable = [
        'variation_name',
        'cost',
        'additional_info',
        'photo',
        'status',
        'item_ID',
    ];
    public $timestamps = false;

}

