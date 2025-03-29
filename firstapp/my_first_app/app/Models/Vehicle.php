<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicle';
    protected $fillable = ['plate_no', 'color', 'vehicle_type', 'model', 'deliveryboy_ID'];
    public $timestamps = false;
    // public function deliveryboy()
    // {
    //     return $this->belongsTo(Deliveryboy::class, 'deliveryboy_ID');
    // }
}
