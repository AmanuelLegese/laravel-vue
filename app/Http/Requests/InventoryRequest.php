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
            'item_id' => 'integer',
            'color_id' => 'integer',
            'size_id' => 'integer',
            'status_id' => 'integer',
            'manufacturer_id' => 'integer',
            'p_date' => 'date',
            'ex_date' => 'date',
            'stock_quantity' => 'integer',
            'single_price' => 'numeric',
            'deleted_at' => 'date',
        ];
    }
}
