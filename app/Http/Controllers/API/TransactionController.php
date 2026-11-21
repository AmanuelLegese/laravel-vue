<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResource
    {
        return TransactionResource::collection(Transaction::latest()->paginate(10));
    }

    /**
     * Sell stock. Batches that are expired or empty are skipped; with item_id
     * the earliest-expiring batches are used first (FEFO).
     */
    public function store(TransactionRequest $request): JsonResource
    {
        $transactions = DB::transaction(function () use ($request) {
            $item = $request->inventory_id
                ? Inventory::findOrFail($request->inventory_id)->item
                : Item::findOrFail($request->item_id);

            $batches = Inventory::query()
                ->where('item_id', $item->id)
                ->when($request->inventory_id, fn ($q) => $q->where('id', $request->inventory_id))
                ->where('stock_quantity', '>', 0)
                ->whereDate('ex_date', '>=', today())
                ->orderBy('ex_date')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('stock_quantity') < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough unexpired stock available.',
                ]);
            }

            $priceId = $item->price()->where('is_active', 1)->value('id');
            $remaining = $request->quantity;
            $created = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, $batch->stock_quantity);
                $batch->decrement('stock_quantity', $take);
                $created[] = Transaction::create([
                    'inventory_id' => $batch->id,
                    'price_id' => $priceId,
                    'user_id' => Auth::id(),
                    'quantity' => $take,
                ]);
                $remaining -= $take;
            }

            return collect($created);
        });

        return TransactionResource::collection($transactions);
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
