<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
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
