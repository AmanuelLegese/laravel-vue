<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    //
    protected $fillable = [
        'name',
    ];

    /**
     * Get the items for the unit.
     */
    public function item(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
