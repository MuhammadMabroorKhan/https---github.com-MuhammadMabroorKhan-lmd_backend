<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'orderdetails';

  
    protected $fillable = [
        'quantities',
        'unitprice',
        'subtotal',
        'order_id',
        'itemdetails_ID',
    ];
    public $timestamps = false;
}

