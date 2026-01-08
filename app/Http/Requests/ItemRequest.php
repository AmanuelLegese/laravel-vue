<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'category_id' => 'integer',
            'unit_id' => 'integer',
            'deleted_at' => 'date',
        ];
    }
}
