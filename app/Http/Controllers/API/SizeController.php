<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SizeController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return SizeResource::collection(Size::latest()->paginate(10));
    }

    public function store(SizeRequest $request): SizeResource|\Illuminate\Http\JsonResponse
    {
        try {
            $size = Size::create($request->validated());
            return new SizeResource($size);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Size $size): SizeResource
    {
        return SizeResource::make($size);
    }

    public function update(SizeRequest $request, Size $size): SizeResource|\Illuminate\Http\JsonResponse
    {
        try {
            $size->update($request->validated());
            return new SizeResource($size);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Size $size): \Illuminate\Http\JsonResponse
    {
        try {
            $size->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
