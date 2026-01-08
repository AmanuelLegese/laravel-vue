<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    //
    protected $fillable = [
        'item_id',
        'amount',
        'is_active',
    ];

    /**
     * Get the item that owns the price.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the transactions for the price.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
