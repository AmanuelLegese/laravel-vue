<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManufacturerController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return ManufacturerResource::collection(Manufacturer::latest()->paginate(10));
    }

    public function store(ManufacturerRequest $request): ManufacturerResource|\Illuminate\Http\JsonResponse
    {
        try {
            $manufacturer = Manufacturer::create($request->validated());
            return new ManufacturerResource($manufacturer);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Manufacturer $manufacturer): ManufacturerResource
    {
        return ManufacturerResource::make($manufacturer);
    }

    public function update(ManufacturerRequest $request, Manufacturer $manufacturer): ManufacturerResource|\Illuminate\Http\JsonResponse
    {
        try {
            $manufacturer->update($request->validated());
            return new ManufacturerResource($manufacturer);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Manufacturer $manufacturer): \Illuminate\Http\JsonResponse
    {
        try {
            $manufacturer->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
