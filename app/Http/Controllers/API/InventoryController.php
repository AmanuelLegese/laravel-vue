<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with(['item:id,name,strength,dosage_form', 'status:id,name', 'manufacturer:id,name'])
            ->when(request('item_id'), fn ($q, $id) => $q->where('item_id', $id))
            ->when(request('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('batch_number', 'like', "%{$term}%")
                ->orWhereHas('item', fn ($i) => $i->where('name', 'like', "%{$term}%"))))
            ->latest()
            ->paginate($this->perPage());

        return InventoryResource::collection($inventories);
    }

    /**
     * Receive a new batch. The initial quantity is logged as a "receipt".
     */
    public function store(InventoryRequest $request, StockService $stock): InventoryResource
    {
        $inventory = DB::transaction(function () use ($request, $stock) {
            $inventory = Inventory::create([...$request->validated(), 'stock_quantity' => 0]);

            if ($request->stock_quantity > 0) {
                $stock->move($inventory, StockMovement::RECEIPT, $request->stock_quantity, 'Batch received');
            }

            return $inventory->refresh();
        });

        return new InventoryResource($inventory);
    }

    public function show(Inventory $inventory): InventoryResource
    {
        return InventoryResource::make($inventory);
    }

    public function update(InventoryRequest $request, Inventory $inventory): InventoryResource
    {
        $inventory->update($request->validated());
        return new InventoryResource($inventory);
    }

    public function destroy(Inventory $inventory): Response
    {
        $inventory->delete();
        return response()->noContent();
    }
}
