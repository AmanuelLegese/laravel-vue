<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //
    protected $fillable = [
        'name',
    ];

    /**
     * Get the inventory records associated with the status.
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
