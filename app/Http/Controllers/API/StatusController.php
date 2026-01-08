<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatusRequest;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return StatusResource::collection(Status::latest()->paginate(10));
    }

    public function store(StatusRequest $request): StatusResource|\Illuminate\Http\JsonResponse
    {
        try {
            $status = Status::create($request->validated());
            return new StatusResource($status);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Status $status): StatusResource
    {
        return StatusResource::make($status);
    }

    public function update(StatusRequest $request, Status $status): StatusResource|\Illuminate\Http\JsonResponse
    {
        try {
            $status->update($request->validated());
            return new StatusResource($status);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Status $status): \Illuminate\Http\JsonResponse
    {
        try {
            $status->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
