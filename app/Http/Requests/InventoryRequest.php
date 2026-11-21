<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
            'item_id' => 'required|integer|exists:items,id',
            'batch_number' => 'required|string|max:100',
            'status_id' => 'required|integer|exists:statuses,id',
            'manufacturer_id' => 'required|integer|exists:manufacturers,id',
            'p_date' => 'required|date|date_format:Y-m-d',
            'ex_date' => ['required','date','date_format:Y-m-d','after:p_date','after:'.Carbon::today()->addMonths(6)->toDateString(),],
            'stock_quantity' => 'required|integer|min:0',
            'single_price' => 'required|numeric|min:0',
        ];
    }
}
