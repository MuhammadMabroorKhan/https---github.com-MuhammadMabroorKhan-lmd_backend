<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BAKERY extends Model
{
    protected $table = 'bakery';

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