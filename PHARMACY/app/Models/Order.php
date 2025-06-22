<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

  
    protected $fillable = [
        'order_date',
        'total_amount',
        'status',
        'delivery_address',
        'paymentstatus',
        'payment_method',
        'order_type',
    ];
    public $timestamps = false;

}

