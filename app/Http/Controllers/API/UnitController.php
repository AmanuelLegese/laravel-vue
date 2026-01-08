<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return UnitResource::collection(Unit::latest()->paginate(10));
    }

    public function store(UnitRequest $request): UnitResource|\Illuminate\Http\JsonResponse
    {
        try {
            $unit = Unit::create($request->validated());
            return new UnitResource($unit);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Unit $unit): UnitResource
    {
        return UnitResource::make($unit);
    }

    public function update(UnitRequest $request, Unit $unit): UnitResource|\Illuminate\Http\JsonResponse
    {
        try {
            $unit->update($request->validated());
            return new UnitResource($unit);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Unit $unit): \Illuminate\Http\JsonResponse
    {
        try {
            $unit->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
