<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    protected $table = 'delivery_tracking';

    // protected $fillable = [
    //     'order_id','status','delivery_boy_id','delivery_boy_name','delivery_boy_contact'
    // ];
    protected $fillable = [
        'order_id',
        'delivery_boy_id',
        'delivery_boy_name',
        'delivery_boy_contact',
        'delivery_boy_image',
    ];

    
    public $timestamps = true;

}
