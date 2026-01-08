<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'inventory_id' => $this->inventory_id,
            'price_id' => $this->price_id,
            'user_id' => $this->user_id,
            'quantity' => $this->quantity,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
