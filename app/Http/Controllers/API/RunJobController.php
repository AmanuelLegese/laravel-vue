<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunJobRequest;
use App\Http\Resources\RunJobResource;
use App\Models\RunJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RunJobController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return RunJobResource::collection(RunJob::latest()->paginate(10));
    }

    public function store(RunJobRequest $request): RunJobResource|\Illuminate\Http\JsonResponse
    {
        try {
            $runJob = RunJob::create($request->validated());
            return new RunJobResource($runJob);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(RunJob $runJob): RunJobResource
    {
        return RunJobResource::make($runJob);
    }

    public function update(RunJobRequest $request, RunJob $runJob): RunJobResource|\Illuminate\Http\JsonResponse
    {
        try {
            $runJob->update($request->validated());
            return new RunJobResource($runJob);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(RunJob $runJob): \Illuminate\Http\JsonResponse
    {
        try {
            $runJob->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
