<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * The only place that changes a batch's stock_quantity. Every change is
 * written to the stock_movements ledger. Call inside a DB transaction.
 */
class StockService
{
    public function move(Inventory $inventory, string $type, int $quantity, ?string $reason = null, ?int $transactionId = null): StockMovement
    {
        $inventory = Inventory::lockForUpdate()->findOrFail($inventory->id);
        $balance = $inventory->stock_quantity + $quantity;

        if ($balance < 0) {
            throw ValidationException::withMessages(['quantity' => 'Stock cannot go below zero.']);
        }

        $inventory->update(['stock_quantity' => $balance]);

        return StockMovement::create([
            'inventory_id' => $inventory->id,
            'user_id' => Auth::id(),
            'transaction_id' => $transactionId,
            'type' => $type,
            'quantity' => $quantity,
            'balance_after' => $balance,
            'reason' => $reason,
        ]);
    }
}
