<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return PriceResource::collection(Price::with('item:id,name,strength')->when(request('item_id'), fn ($q, $id) => $q->where('item_id', $id))->latest()->paginate($this->perPage()));
    }

    public function store(PriceRequest $request): PriceResource|\Illuminate\Http\JsonResponse
    {
        try {
            $price = DB::transaction(function () use ($request) {
                        // Deactivate existing prices for the item(s)
                        Price::whereIn('item_id', [$request->item_id])
                            ->update(['is_active' => 0]);

                        // Create the new active price
                        return Price::create($request->validated());
                    });
            return new PriceResource($price);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Price $price): PriceResource
    {
        return PriceResource::make($price);
    }

    public function update(PriceRequest $request, Price $price): PriceResource|\Illuminate\Http\JsonResponse
    {
        try {
            $price->update($request->validated());
            return new PriceResource($price);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Price $price): \Illuminate\Http\JsonResponse
    {
        try {
            $price->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
