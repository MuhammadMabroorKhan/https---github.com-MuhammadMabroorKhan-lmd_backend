<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = ['delivery_address', 'order_date', 'total_amount', 'order_status', 'estimated_delivery_time', 'delivery_time', 'payment_status', 'customer_id'];
    public $timestamps = false;
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function suborders()
    {
        return $this->hasMany(Suborder::class, 'order_ID');
    }
}
