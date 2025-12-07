<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return TransactionResource::collection(Transaction::latest()->paginate(10));
    }

    public function store(TransactionRequest $request): TransactionResource|\Illuminate\Http\JsonResponse
    {
        try {
            $transaction = Transaction::create($request->validated());
            return new TransactionResource($transaction);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return TransactionResource::make($transaction);
    }

    public function update(TransactionRequest $request, Transaction $transaction): TransactionResource|\Illuminate\Http\JsonResponse
    {
        try {
            $transaction->update($request->validated());
            return new TransactionResource($transaction);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Transaction $transaction): \Illuminate\Http\JsonResponse
    {
        try {
            $transaction->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
