<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * stock_quantity is only accepted when a batch is first received.
     * Later changes go through POST /stock-adjustments so they are logged.
     */
    public function rules(): array
    {
        $rules = [
            'item_id' => 'required|integer|exists:items,id',
            'status_id' => 'required|integer|exists:statuses,id',
            'manufacturer_id' => 'required|integer|exists:manufacturers,id',
            'batch_number' => [
                'required', 'string', 'max:100',
                Rule::unique('inventories')
                    ->where('item_id', $this->input('item_id'))
                    ->whereNull('deleted_at')
                    ->ignore($this->route('inventory')),
            ],
            'p_date' => 'required|date|date_format:Y-m-d',
            'ex_date' => 'required|date|date_format:Y-m-d|after:p_date|after:today',
            'cost_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'purchase_order' => 'nullable|string|max:100',
        ];

        if ($this->isMethod('POST')) {
            $rules['stock_quantity'] = 'required|integer|min:0';
        }

        return $rules;
    }
}
