<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryboyRating extends Model
{
    protected $table = 'deliveryboysrating';
    protected $fillable = ['comments', 'rating_stars', 'rating_date', 'deliveryboy_ID', 'customer_id', 'suborder_id'];
    public $timestamps = false;
    public function deliveryboy()
    {
        return $this->belongsTo(Deliveryboy::class, 'deliveryboy_ID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function suborder()
    {
        return $this->belongsTo(Suborder::class, 'suborder_id');
    }
}
