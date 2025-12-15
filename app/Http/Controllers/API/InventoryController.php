<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    public function index()
    {
        return InventoryResource::collection(Inventory::latest()->paginate(10));
    }

    public function store(InventoryRequest $request): InventoryResource
    {
            $inventory = Inventory::create($request->validated());
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
