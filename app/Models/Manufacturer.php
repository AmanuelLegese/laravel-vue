<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'name',
        'description'
    ];

    protected $casts = [
        'created_at'=> 'date',
        'updated_at'=> 'date',
    ];
}
