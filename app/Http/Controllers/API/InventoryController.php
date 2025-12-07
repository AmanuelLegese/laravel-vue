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
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return InventoryResource::collection(Inventory::latest()->paginate(10));
    }

    public function store(InventoryRequest $request): InventoryResource|\Illuminate\Http\JsonResponse
    {
        try {
            $inventory = Inventory::create($request->validated());
            return new InventoryResource($inventory);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Inventory $inventory): InventoryResource
    {
        return InventoryResource::make($inventory);
    }

    public function update(InventoryRequest $request, Inventory $inventory): InventoryResource|\Illuminate\Http\JsonResponse
    {
        try {
            $inventory->update($request->validated());
            return new InventoryResource($inventory);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Inventory $inventory): \Illuminate\Http\JsonResponse
    {
        try {
            $inventory->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
