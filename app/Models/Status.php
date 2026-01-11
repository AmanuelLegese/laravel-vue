<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use SoftDeletes, HasFactory;
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
