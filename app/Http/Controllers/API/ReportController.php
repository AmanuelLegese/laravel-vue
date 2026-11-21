<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Items whose unexpired stock is at or below their reorder level.
     */
    public function lowStock(): JsonResponse
    {
        $items = Item::query()
            ->withSum(['inventory as stock' => fn ($q) => $q->whereDate('ex_date', '>=', today())], 'stock_quantity')
            ->get()
            ->map(function (Item $item) {
                $item->stock = (int) $item->stock;
                return $item;
            })
            ->filter(fn (Item $item) => $item->stock <= $item->reorder_level)
            ->values();

        return response()->json(['data' => $items]);
    }

    /**
     * Batches with stock that are expired or expire within ?days (default 90).
     */
    public function expiring(Request $request): JsonResponse
    {
        $days = max(0, (int) $request->query('days', 90));

        $batches = Inventory::with('item:id,name,strength,dosage_form')
            ->where('stock_quantity', '>', 0)
            ->whereDate('ex_date', '<=', today()->addDays($days))
            ->orderBy('ex_date')
            ->get();

        return response()->json(['data' => $batches]);
    }
}
