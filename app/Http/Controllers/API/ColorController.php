<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ColorController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return ColorResource::collection(Color::latest()->paginate(10));
    }

    public function store(ColorRequest $request): ColorResource|\Illuminate\Http\JsonResponse
    {
        try {
            $color = Color::create($request->validated());
            return new ColorResource($color);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Color $color): ColorResource
    {
        return ColorResource::make($color);
    }

    public function update(ColorRequest $request, Color $color): ColorResource|\Illuminate\Http\JsonResponse
    {
        try {
            $color->update($request->validated());
            return new ColorResource($color);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Color $color): \Illuminate\Http\JsonResponse
    {
        try {
            $color->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
