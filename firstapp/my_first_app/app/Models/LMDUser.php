<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LMDUser extends Model
{
    protected $table = 'lmd_users';
    protected $fillable = ['name', 'email', 'phone_no', 'password', 'street', 'city', 'state', 'zip_code', 'latitude', 'longitude', 'user_role', 'account_creation_date'];
    public $timestamps = false;
    // Relationship to customers
    public function customer()
    {
        return $this->hasOne(Customer::class, 'users_ID');
    }

    // Relationship to deliveryboys
    public function deliveryboy()
    {
        return $this->hasOne(Deliveryboy::class, 'users_ID');
    }
}
