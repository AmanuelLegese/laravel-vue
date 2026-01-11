<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use SoftDeletes, HasFactory;
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
