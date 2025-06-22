<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';

  
    protected $fillable = [
        'itemdetails_ID',
        'stock_qty',
        'last_updated',
    ];
    public $timestamps = false;

}