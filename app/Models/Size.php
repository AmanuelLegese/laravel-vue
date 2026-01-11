<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Size extends Model
{
        use SoftDeletes, HasFactory;
    //
    protected $fillable = [
        'name',
    ];

    /**
     * Get the inventory records associated with the size.
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
