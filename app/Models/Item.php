<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes, HasFactory;
    //
    protected $fillable = [
        'name',
        'category_id',
        'unit_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * has many inventory
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * has many transaction
     */
    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * belongs to category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * belongs to unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * has many price
     */
    public function price()
    {
        return $this->hasMany(Price::class);
    }
}
