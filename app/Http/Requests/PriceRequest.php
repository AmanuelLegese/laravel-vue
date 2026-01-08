<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'integer',
            'amount' => 'numeric',
            'is_active' => 'integer',
            'deleted_at' => 'date',
        ];
    }
}
