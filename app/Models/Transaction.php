<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
        
    ];

    /**
     * Get the item that owns the transaction.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
