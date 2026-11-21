<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sell either a specific batch (inventory_id) or an item (item_id).
     * With item_id, stock is taken from the earliest-expiring batches first.
     */
    public function rules(): array
    {
        return [
            'inventory_id' => ['required_without:item_id', 'nullable', 'integer', 'exists:inventories,id'],
            'item_id' => ['required_without:inventory_id', 'nullable', 'integer', 'exists:items,id'],
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $item = $this->inventory_id
                ? Inventory::find($this->inventory_id)?->item
                : Item::find($this->item_id);

            if (! $item?->price()->where('is_active', 1)->exists()) {
                $validator->errors()->add('item_id', 'The selected item does not have an active price.');
            }
        });
    }
}
