<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TransactionResource;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\StockService;
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
        return TransactionResource::collection(
            Transaction::with(['inventory.item:id,name,strength', 'invoice:id,invoice_number', 'user:id,name'])->latest()->paginate($this->perPage())
        );
    }

    /**
     * Sell stock and return an invoice. Batches that are expired, empty or
     * not sellable (status) are skipped; with item_id the earliest-expiring
     * batches are used first (FEFO). One transaction line is created per batch.
     */
    public function store(TransactionRequest $request, StockService $stock): InvoiceResource
    {
        $invoice = DB::transaction(function () use ($request, $stock) {
            $item = $request->inventory_id
                ? Inventory::findOrFail($request->inventory_id)->item
                : Item::findOrFail($request->item_id);

            $batches = Inventory::query()
                ->where('item_id', $item->id)
                ->when($request->inventory_id, fn ($q) => $q->where('id', $request->inventory_id))
                ->where('stock_quantity', '>', 0)
                ->whereDate('ex_date', '>=', today())
                ->whereHas('status', fn ($q) => $q->where('is_sellable', true))
                ->orderBy('ex_date')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('stock_quantity') < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough sellable, unexpired stock available.',
                ]);
            }

            $price = $item->price()->where('is_active', 1)->first();

            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'patient_name' => $request->patient_name,
                'prescriber' => $request->prescriber,
                'prescription_number' => $request->prescription_number,
            ]);
            $invoice->update(['invoice_number' => sprintf('INV-%06d', $invoice->id)]);

            $remaining = $request->quantity;
            $total = 0;

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, $batch->stock_quantity);
                $lineTotal = round($price->amount * $take, 2);

                $line = Transaction::create([
                    'invoice_id' => $invoice->id,
                    'inventory_id' => $batch->id,
                    'price_id' => $price->id,
                    'user_id' => Auth::id(),
                    'quantity' => $take,
                    'unit_price' => $price->amount,
                    'line_total' => $lineTotal,
                ]);
                $stock->move($batch, StockMovement::SALE, -$take, $invoice->invoice_number, $line->id);

                $total += $lineTotal;
                $remaining -= $take;
            }

            $invoice->update(['total' => $total]);

            return $invoice->load('transactions');
        });

        return InvoiceResource::make($invoice);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return TransactionResource::make($transaction);
    }

    /**
     * Void a sale line: the quantity goes back to its batch and the invoice
     * total is reduced. Sales are never edited in place.
     */
    public function destroy(Transaction $transaction, StockService $stock): Response
    {
        DB::transaction(function () use ($transaction, $stock) {
            $stock->move($transaction->inventory, StockMovement::VOID, $transaction->quantity, 'Sale voided', $transaction->id);

            if ($transaction->invoice) {
                $transaction->invoice->decrement('total', $transaction->line_total ?? 0);
            }

            $transaction->delete();
        });

        return response()->noContent();
    }
}
