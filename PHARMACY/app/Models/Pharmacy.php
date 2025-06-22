<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    protected $table = 'pharmacy';

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