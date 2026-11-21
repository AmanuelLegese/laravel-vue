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
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'unit_id' => 'required|integer|exists:units,id',
            'generic_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:50',
            'dosage_form' => 'nullable|string|max:50',
            'reorder_level' => 'required|integer|min:0',
        ];
    }
}
