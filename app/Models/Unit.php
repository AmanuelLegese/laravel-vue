<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes, HasFactory;
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
