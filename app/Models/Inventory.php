<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'item_id',
        'manufacturer_id',
        'unit',
        'unit_price',
        'quantity',
        'remaining_quantity',
        'mfd',
        'exp',
    ];

    protected $casts = [
        'mfd' => 'date',
        'exp' => 'date',
    ];
    /**
     * Get the item that owns the inventory.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the inventory transactions for the inventory.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the supplier that provided the inventory.
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
