<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'integer|required|exists:items,id',
            'manufacturer_id' => 'integer|required|exists:manufacturers,id',
            'unit' => 'string|required|max:50',
            'unit_price' => 'numeric|required|min:1',
            'quantity' => 'integer|required|min:1',
            'remaining_quantity' => 'numeric|required|min:0',
            'mfd' => 'date||required',
            'exp' => 'date||required',
            'deleted_at' => 'date',
        ];
    }
}
