<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->item_id,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'remaining_quantity' => $this->remaining_quantity,
            'mfd' => $this->mfd,
            'exp' => $this->exp,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
