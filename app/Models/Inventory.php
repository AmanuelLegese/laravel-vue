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
        'color_id',
        'size_id',
        'status_id',
        'stock_quantity',
        'single_price',
    ];

    /**
     * Get the item that owns the inventory.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the color that owns the inventory.
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the size that owns the inventory.
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the status that owns the inventory.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the transactions for the inventory.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}
