<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_id' => 'integer',
            'price_id' => 'integer',
            'user_id' => 'integer',
            'quantity' => 'integer',
            'deleted_at' => 'date',
        ];
    }
}
