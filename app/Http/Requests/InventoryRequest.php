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
            'color_id' => 'required|integer|exists:colors,id',
            'size_id' => 'required|integer|exists:sizes,id',
            'status_id' => 'required|integer|exists:statuses,id',
            'manufacturer_id' => 'required|integer|exists:manufacturers,id',
            'p_date' => 'required|date|date_format:Y-m-d',
            'ex_date' => ['required','date','date_format:Y-m-d','after:p_date','after:'.Carbon::today()->addMonths(6)->toDateString(),],
            'stock_quantity' => 'required|integer',
            'single_price' => 'required|numeric',
        ];
    }
}
