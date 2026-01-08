<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return PriceResource::collection(Price::latest()->paginate(10));
    }

    public function store(PriceRequest $request): PriceResource|\Illuminate\Http\JsonResponse
    {
        try {
            $price = Price::create($request->validated());
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
