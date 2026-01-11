<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use SoftDeletes, HasFactory;
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
