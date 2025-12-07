<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
        $this->logTransactionEvent($transaction, 'created');
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
        $this->logTransactionEvent($transaction, 'updated');
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
        $this->logTransactionEvent($transaction, 'deleted');
    }
    public function deleting(Transaction $transaction): void
    {
        //
        $this->logTransactionEvent($transaction, 'deleting');
    }
    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
            $this->logTransactionEvent($transaction, 'restored');
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
        $this->logTransactionEvent($transaction, 'force deleted');
    }

    /**
     * Helper method to log transaction events.
     */
    private function logTransactionEvent(Transaction $transaction, string $event): void
    {
        $logMessage = "Transaction {$event}:";

        Log::info($logMessage, [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'item_id' => $transaction->item_id,
            'quantity' => $transaction->quantity,
            'type' => $transaction->type,
            'created_at' => $transaction->created_at,
        ]);
    }
}
