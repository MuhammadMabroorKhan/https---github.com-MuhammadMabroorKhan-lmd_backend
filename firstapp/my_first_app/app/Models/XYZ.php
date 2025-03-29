<?php

namespace App\Models;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class XYZ extends Model
{
    use HasFactory;
    protected $table = 'XYZ';  // Explicitly set the table name
    // protected $primaryKey = 'id';  // Set the primary key

    // If your primary key is not auto-incrementing or isn't named 'id'
    // public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = ['name', 'age', 'gender', 'city']; // List all columns that can be mass assigned
}


