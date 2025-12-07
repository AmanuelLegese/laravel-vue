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
            'user_id' => 'integer|required|exists:users,id',
            'item_id' => 'integer|required|exists:items,id',
            'quantity' => 'integer|required|min:1',
            'deleted_at' => 'date',
        ];
    }
}
