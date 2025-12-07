<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * has many
     */

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
}
