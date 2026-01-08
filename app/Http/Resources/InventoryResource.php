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
            'color_id' => $this->color_id,
            'size_id' => $this->size_id,
            'status_id' => $this->status_id,
            'manufacturer_id' => $this->manufacturer_id,
            'p_date' => $this->p_date,
            'ex_date' => $this->ex_date,
            'stock_quantity' => $this->stock_quantity,
            'single_price' => $this->single_price,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
