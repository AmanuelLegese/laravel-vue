<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->item_id,
            'amount' => $this->amount,
            'is_active' => $this->is_active,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
