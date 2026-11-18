# Inventory & Finance Module

Scope: `inventories`, `transactions`, `prices` (plus the lookup tables they reference). Describes what the code does today, how it compares with common pharmacy inventory practice, the known gaps, and a proposed design for invoice generation (section 5, not yet built).

Sources for the practice notes: CPCON Group, "Pharmacy Inventory Management", and Fishbowl, "Pharmacy Inventory Management". (The AccessPharmacy chapter could not be retrieved and is not used.)

---

## 1. Data model

```
categories ─┐
units ──────┼─► items ─┬─► prices ◄──────────┐
            │          │                      │
            │          └─► inventories ─► transactions ─► users
colors, sizes, statuses, manufacturers ─►┘
```

| Table | Purpose | Key columns |
|---|---|---|
| `items` | Product master | `name`, `category_id`, `unit_id` |
| `prices` | Selling price history per item | `item_id`, `amount` decimal(10,2), `is_active` |
| `inventories` | One stock row (a batch-like record) per item/colour/size/manufacturer | `p_date`, `ex_date`, `stock_quantity`, `single_price` decimal(10,2), `status_id` |
| `transactions` | A stock-out event | `inventory_id`, `price_id`, `user_id`, `quantity` |

All four use soft deletes. Foreign keys cascade on delete.

Money is stored as `decimal(10,2)`, which is correct. There is no currency, tax, discount, or stored line total.

## 2. API

All routes are under `auth:api` and are standard `apiResource`s: `/inventories`, `/transactions`, `/prices`, `/items`. Lists are paginated 10 per page, newest first.

### Inventories (`InventoryController`)
- Create, update, and delete are plain CRUD on `InventoryRequest`.
- Validation: `ex_date` must be after `p_date` **and** after today + 6 months. This enforces a minimum shelf life on receipt, so short-dated stock cannot be entered.
- `stock_quantity` and `single_price` are validated only as `integer` / `numeric` (no minimum).

### Prices (`PriceController`)
- `POST /prices` runs in a DB transaction. It sets `is_active = 0` on all of the item's existing prices, then inserts the new price as active. So each item has one active price, and prior prices are kept as history.

### Transactions (`TransactionController`)
`POST /transactions`:
1. `TransactionRequest` ignores any client-sent `user_id` and `price_id`. It sets `user_id` from the authenticated user and `price_id` from the item's active price. It rejects the request if the item has no active price.
2. In a DB transaction the controller loads the inventory row, rejects if `stock_quantity < quantity`, decrements stock, and creates the transaction.

Line value is `transaction.quantity × price.amount`. It is not stored, so it must be derived by joining `prices`.

## 3. How this compares with common practice

| Practice | Status |
|---|---|
| Perpetual tracking: every movement updates on-hand count | Partial. Stock-out via `/transactions` does; direct edits to `stock_quantity` and receipts do not leave a record |
| Expiry date on every stock record | Done (`ex_date`) |
| First-expiry-first-out (FEFO) dispensing | Not implemented. The caller chooses `inventory_id` |
| Expiry alerts (about 90/60/30 days) | Not implemented |
| Lot/batch number for recalls | Missing |
| Par / reorder level and low-stock alerts | Missing |
| Audit trail of who moved what | Partial. `user_id` on stock-outs only |
| Waste, returns, adjustments, transfers | Missing. Only outflow exists |
| Controlled-substance schedule and running balance | Missing |
| Price history | Done (inactive prices retained), see gap 4 |
| KPIs (turnover, days on hand, stockout rate, waste rate) | Not implemented. Derivable once the gaps below are closed |

## 4. Known gaps and risks

Ordered by impact on stock and money correctness.

1. **Race condition on stock-out.** The stock check reads `stock_quantity` without a row lock, then decrements. Two concurrent requests can both pass the check and drive stock negative. Fix: `Inventory::lockForUpdate()->findOrFail()` inside the transaction, or a conditional `UPDATE ... WHERE stock_quantity >= ?`.
2. **Transaction update/delete do not touch stock.** `PUT /transactions/{id}` can change `quantity` and `DELETE` removes the record, but neither adjusts `stock_quantity`, so the count drifts from the ledger. Prefer making transactions immutable and recording reversals as new entries.
3. **Stock can be edited directly.** `PUT /inventories/{id}` accepts any `stock_quantity` with no record of the change. Receipts and adjustments should be ledger entries, not overwrites.
4. **Historical prices can be rewritten.** `PUT /prices/{id}` changes `amount` on a row that past transactions point to, which changes the recorded value of past sales. Fix: treat prices as immutable, or snapshot the unit price on the transaction.
5. **Two price fields.** `inventories.single_price` and `prices.amount` both exist. Transactions use only `prices.amount`. Document whether `single_price` is unit cost (then margin = `prices.amount - single_price`) or remove it.
6. **No transaction type.** Everything is a positive-quantity stock-out, so returns, waste, expiry write-offs and receipts have nowhere to go. Add a `type` column (receipt, sale, return, waste, adjustment) and sign the quantity.
7. **`manufacturer_id` is not a real foreign key.** The migration is missing `constrained()`, so the database does not enforce it.
8. **Weak input validation.** `stock_quantity` and `single_price` accept negatives; `quantity` is `numeric` (allows decimals) but the column is integer.
9. **Fragile request preparation.** `prepareForValidation` dereferences `$inventory->item->price->firstWhere(...)->id` before validation runs. A missing inventory or an item with no active price raises an error (500) instead of a 422.
10. **Generic exception on insufficient stock.** `throw new \Exception('Not enough stock available.')` returns a 500. Use a validation or 422 response.
11. **`Transaction` casts `created_at` to `date`,** dropping the time of day. That loses ordering within a day and shift-level reconciliation.
12. **`status_id` has no behaviour.** Nothing checks the status (for example quarantined or expired) before selling.

## 5. Invoice generation (proposed, not implemented)

Today no invoice exists: a transaction is a single stock-out line and nothing groups lines together. This section is the design for adding invoices. It depends on fixing gaps 1 and 4 first, because an invoice must not change after it is issued.

### 5.1 Data model

```
customers ─► invoices ─► invoice_items ─► inventories
                │              │
                │              └─► transactions (1:1, the stock-out)
                └─► users (issued_by)
```

| Table | Columns |
|---|---|
| `customers` | `name`, `phone`, `email`, `address` (nullable; walk-in sales may have no customer) |
| `invoices` | `invoice_number` (unique), `customer_id` nullable, `issued_by` (user), `issued_at`, `subtotal`, `discount_total`, `tax_total`, `total`, `status` (`draft`, `issued`, `paid`, `void`), `paid_at` nullable |
| `invoice_items` | `invoice_id`, `inventory_id`, `transaction_id`, `description` (item name at time of sale), `quantity`, `unit_price`, `discount`, `tax_rate`, `line_total` |

Rules:
- `unit_price`, `description`, and all totals are **copied onto the invoice** when it is issued. Later price or item edits must not change an issued invoice.
- Money columns are `decimal(10,2)`, as elsewhere.
- Invoice numbers come from a dedicated sequence (for example `INV-2026-000123`), assigned inside the same DB transaction as the invoice, and never reused. A mistaken invoice is set to `void`, not deleted.
- Issued invoices are immutable: no `PUT`, and `DELETE` is disallowed. Corrections are a void plus a new invoice (or a credit note, later).

### 5.2 Endpoints

All under `auth:api`.

| Method | Path | Behaviour |
|---|---|---|
| `POST` | `/invoices` | Create and issue an invoice from a list of `{inventory_id, quantity}` lines plus optional `customer_id` and discounts |
| `GET` | `/invoices` | Paginated list; filter by `status`, `customer_id`, date range |
| `GET` | `/invoices/{id}` | Invoice with its lines |
| `GET` | `/invoices/{id}/pdf` | Printable or downloadable PDF |
| `POST` | `/invoices/{id}/void` | Void an invoice and return its stock (see below) |
| `POST` | `/invoices/{id}/pay` | Mark as paid (sets `paid_at`) |

`POST /invoices` request:

```json
{
  "customer_id": 12,
  "lines": [
    { "inventory_id": 5, "quantity": 2 },
    { "inventory_id": 9, "quantity": 1 }
  ]
}
```

### 5.3 Issue flow

Everything runs in one DB transaction, so an invoice is either fully created with its stock deducted, or nothing changes:

1. Validate the lines. Each `inventory_id` must exist and its item must have an active price. Reject the whole request with a 422 if any line fails.
2. For each line, lock the inventory row (`lockForUpdate`), check `stock_quantity >= quantity`, and decrement it.
3. Create the `transactions` row (reusing the existing stock-out logic) and an `invoice_items` row that snapshots the active price.
4. Compute line totals, then the invoice totals.
5. Assign the next invoice number and save the invoice as `issued`.

Calculation: `line_total = quantity × unit_price − discount + tax`, `subtotal = Σ(quantity × unit_price)`, and `total = subtotal − discount_total + tax_total`. Round at line level to two decimals so lines always sum to the total.

### 5.4 Void flow

Voiding sets `status = void`, and for each line adds the quantity back to `inventories.stock_quantity`. This needs the `type` column from gap 6 so the return is recorded as a ledger entry (for example `type = return`) rather than an unrecorded edit. Do not return stock for items that are expired or quarantined; route those to waste instead.

### 5.5 PDF

Use `barryvdh/laravel-dompdf` (or similar) to render a Blade view with: business name and address, invoice number and date, customer, line table (description, quantity, unit price, line total), totals, and payment status. Generate on request; do not store the PDF, since it can be regenerated from the immutable invoice.

### 5.6 Pharmacy considerations

- Include the lot number and expiry date on each line once `lot_number` exists (see next steps), so a recall can be traced to the customers who received the product.
- Controlled substances need a patient or prescriber reference on the line. This is outside this design and should be decided with whoever owns compliance.
- Restrict `POST /invoices`, `void` and `pay` to specific roles using the existing role and permission setup.

## 6. Suggested next steps

1. Fix gaps 1, 7, 9 and 10 (small, no schema redesign beyond the FK).
2. Add `type` to `transactions` and route receipts and adjustments through it (gaps 2, 3, 6).
3. Snapshot `unit_price` on `transactions` and make prices immutable (gap 4).
4. Build invoices (section 5): migrations, models, `InvoiceRequest`, `InvoiceController`, tests, then PDF.
5. Add `lot_number`, `reorder_level` and an expiring-soon query/endpoint.
6. Add reports: stock on hand, value on hand (`stock_quantity × single_price`), sales by period, expiring stock, low stock.
