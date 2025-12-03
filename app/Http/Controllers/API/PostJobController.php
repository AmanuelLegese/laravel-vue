<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostJobRequest;
use App\Http\Resources\PostJobResource;
use App\Models\PostJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostJobController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return PostJobResource::collection(PostJob::latest()->paginate(10));
    }

    public function store(PostJobRequest $request): PostJobResource|\Illuminate\Http\JsonResponse
    {
        try {
            $postJob = PostJob::create($request->validated());
            return new PostJobResource($postJob);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PostJob $postJob): PostJobResource
    {
        return PostJobResource::make($postJob);
    }

    public function update(PostJobRequest $request, PostJob $postJob): PostJobResource|\Illuminate\Http\JsonResponse
    {
        try {
            $postJob->update($request->validated());
            return new PostJobResource($postJob);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PostJob $postJob): \Illuminate\Http\JsonResponse
    {
        try {
            $postJob->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
