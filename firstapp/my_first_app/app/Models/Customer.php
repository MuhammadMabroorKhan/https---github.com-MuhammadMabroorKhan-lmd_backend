<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = ['preferred_payment', 'users_ID'];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(LmdUser::class, 'users_ID');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
