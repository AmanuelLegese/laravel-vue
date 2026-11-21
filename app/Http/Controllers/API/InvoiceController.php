<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['user:id,name', 'transactions.inventory.item:id,name,strength'])
            ->when(request('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('invoice_number', 'like', "%{$term}%")
                ->orWhere('patient_name', 'like', "%{$term}%")))
            ->latest()
            ->paginate($this->perPage());

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return InvoiceResource::make($invoice->load(['user:id,name', 'transactions.inventory.item:id,name,strength']));
    }
}
