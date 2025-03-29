<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suborder extends Model
{
    protected $table = 'suborder';
    protected $fillable = ['status', 'vendor_type', 'vendor_order_ID', 'total_amount', 'order_ID'];
    public $timestamps = false;
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_ID');
    }

    public function ratings()
    {
        return $this->hasMany(DeliveryboyRating::class, 'suborder_id');
    }
}
