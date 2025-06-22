<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'orderdetails';

  
    protected $fillable = [
        'qty',
        'unit_price',
        'subtotal',
        'order_id',
        'item_detail_id',
    ];
    public $timestamps = false;
}

