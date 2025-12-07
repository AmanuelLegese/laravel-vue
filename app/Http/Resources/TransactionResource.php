<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'item_id' => $this->item_id,
            'quantity' => $this->quantity,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
