<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    /**
     * Stock history. Filters: inventory_id, item_id, type, from, to (Y-m-d).
     */
    public function movements(Request $request)
    {
        $request->validate([
            'inventory_id' => 'nullable|integer',
            'item_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $movements = StockMovement::with('inventory:id,item_id,batch_number', 'inventory.item:id,name', 'user:id,name')
            ->when($request->inventory_id, fn ($q, $v) => $q->where('inventory_id', $v))
            ->when($request->item_id, fn ($q, $v) => $q->whereHas('inventory', fn ($i) => $i->where('item_id', $v)))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')
            ->paginate(20);

        return response()->json($movements);
    }

    /**
     * Write off or correct stock, or record a customer return. A reason is required.
     * damaged/expired/lost subtract, return adds, correction uses the signed quantity.
     */
    public function adjust(Request $request, StockService $stock): JsonResponse
    {
        $data = $request->validate([
            'inventory_id' => 'required|integer|exists:inventories,id',
            'type' => ['required', Rule::in([
                StockMovement::DAMAGED, StockMovement::EXPIRED, StockMovement::LOST,
                StockMovement::RETURN, StockMovement::CORRECTION,
            ])],
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $delta = match ($data['type']) {
            StockMovement::DAMAGED, StockMovement::EXPIRED, StockMovement::LOST => -abs($data['quantity']),
            StockMovement::RETURN => abs($data['quantity']),
            default => $data['quantity'],
        };

        $movement = DB::transaction(fn () => $stock->move(
            Inventory::findOrFail($data['inventory_id']), $data['type'], $delta, $data['reason']
        ));

        return response()->json(['data' => $movement], 201);
    }
}
