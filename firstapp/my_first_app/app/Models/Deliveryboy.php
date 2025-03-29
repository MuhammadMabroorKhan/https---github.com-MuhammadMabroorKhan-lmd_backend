<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deliveryboy extends Model
{
    protected $table = 'deliveryboys';
    protected $fillable = ['license_photo', 'total_deliveries', 'cnic_no', 'profile_image_url', 'availability_status', 'license_expiration_date', 'users_ID'];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(LmdUser::class, 'users_ID');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'deliveryboy_ID');
    }

    public function ratings()
    {
        return $this->hasMany(DeliveryboyRating::class, 'deliveryboy_ID');
    }
}
