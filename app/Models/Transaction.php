<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'inventory_id',
        'price_id',
        'quantity',
    ];

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
        
    ];
    
    /**
     * Get the price that owns the transaction.
     */
    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the inventory that owns the transaction.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

}
