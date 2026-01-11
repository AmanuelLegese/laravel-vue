<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_id' => ['required','integer','exists:inventories,id'],
            'quantity' => 'required|numeric|min:1',
            'price_id' => 'required|integer|exists:prices,id',
            'user_id' => 'required|integer|exists:users,id'
        ];
    }

    public function prepareForValidation()
    {
        $inventory = Inventory::find($this->input('inventory_id'));
        $user_id = Auth::id();
        return $this->merge([
            'price_id' => $inventory->item->price->firstWhere('is_active', 1)->id,
            'user_id' => $user_id

        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
        $inventory = Inventory::find($this->input('inventory_id'));
        $hasActivePrice = $inventory?->item?->price?->contains(fn ($price) => $price->is_active == 1);

            if (! $hasActivePrice) {
                $validator->errors()->add(
                    'inventory_id',
                    'The selected inventory does not have an active price.'
                );
            }
        });
    }
}
