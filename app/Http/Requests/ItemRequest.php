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
            'name' => 'string|required|max:255|unique:items,name',
            'description' => 'string',
            'deleted_at' => 'date',
        ];
    }
}
