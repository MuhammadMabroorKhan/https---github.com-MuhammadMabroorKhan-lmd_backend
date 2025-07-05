<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRating extends Model
{
    protected $table = 'itemrating';

   
    protected $fillable = [
        'stars',
        'comments',
        'ratingdate',
        'order_ID',
        'item_detail_ID',
    ];
    public $timestamps = false;

}

