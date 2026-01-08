<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    //
    protected $fillable = [
        'name',
    ];

    /**
     * Get the inventory records associated with the color.
     */
    public function inventory() : HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
