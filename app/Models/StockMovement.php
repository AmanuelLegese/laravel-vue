<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const OPENING = 'opening';
    public const RECEIPT = 'receipt';
    public const SALE = 'sale';
    public const VOID = 'void';
    public const RETURN = 'return';
    public const DAMAGED = 'damaged';
    public const EXPIRED = 'expired';
    public const LOST = 'lost';
    public const CORRECTION = 'correction';

    protected $fillable = [
        'inventory_id',
        'user_id',
        'transaction_id',
        'type',
        'quantity',
        'balance_after',
        'reason',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
