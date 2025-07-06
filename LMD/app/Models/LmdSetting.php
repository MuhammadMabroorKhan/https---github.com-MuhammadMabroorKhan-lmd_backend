<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmdSetting extends Model
{
    protected $table = 'lmdsettings';

    protected $fillable = ['key', 'value'];
}
