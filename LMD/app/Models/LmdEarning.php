<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmdEarning extends Model
{
    protected $table = 'lmd_earnings';

    protected $fillable = [
        'suborder_id',
        'order_total',
        'lmd_charge_percentage',
        'lmd_earning_amount',
        'tax_percentage',
        'tax_amount',
    ];

   
}

