<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FURNITURE extends Model
{
    protected $table = 'FURNITURE';

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