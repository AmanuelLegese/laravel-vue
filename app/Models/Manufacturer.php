<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
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
