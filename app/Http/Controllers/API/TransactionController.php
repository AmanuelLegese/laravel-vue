<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResource
    {
        return TransactionResource::collection(Transaction::latest()->paginate(10));
    }

    public function store(TransactionRequest $request): TransactionResource
    {
        $transaction = DB::transaction(function() use ($request)
            {
                    $inventory = Inventory::findOrFail($request->inventory_id);

                    // Check if enough stock is available
                    if ($inventory->stock_quantity < $request->quantity) {
                        throw new \Exception('Not enough stock available.');
                    }

                    // Decrement stock safely
                    $inventory->decrement('stock_quantity', $request->quantity);
                    
                    return Transaction::create($request->validated());
                });
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
