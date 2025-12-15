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
    public function index()
    {
        return TransactionResource::collection(Transaction::latest()->paginate(10));
    }

    public function store(TransactionRequest $request): TransactionResource
    {
            $transaction = Transaction::create($request->validated());
            return new TransactionResource($transaction);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return TransactionResource::make($transaction);
    }

    public function update(TransactionRequest $request, Transaction $transaction): TransactionResource
    {
            $transaction->update($request->validated());
            return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction): Response
    {
            $transaction->delete();
            return response()->noContent();
    }
}
