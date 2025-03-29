<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resturant extends Model
{
    protected $table = 'restaurant';

    protected $fillable = [
        'name',
        'description',
        'location',
        'phone_number',
        'email',
        'status'
    ];
    public $timestamps = false;
}