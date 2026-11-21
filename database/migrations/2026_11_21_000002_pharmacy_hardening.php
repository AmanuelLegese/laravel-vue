<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_prescription_only')->default(false);
            $table->boolean('is_controlled')->default(false);
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->boolean('is_sellable')->default(true);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->renameColumn('single_price', 'cost_price');
            $table->string('supplier')->nullable();
            $table->string('purchase_order', 100)->nullable();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->cascadeOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('patient_name')->nullable();
            $table->string('prescriber')->nullable();
            $table->string('prescription_number', 100)->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('line_total', 10, 2)->nullable();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['inventory_id', 'created_at']);
        });

        // Opening balance for stock that existed before the ledger.
        DB::table('inventories')->where('stock_quantity', '>', 0)->orderBy('id')->each(function ($batch) {
            DB::table('stock_movements')->insert([
                'inventory_id' => $batch->id,
                'type' => 'opening',
                'quantity' => $batch->stock_quantity,
                'balance_after' => $batch->stock_quantity,
                'reason' => 'Opening balance',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::dropIfExists('colors');
        Schema::dropIfExists('sizes');
    }

    public function down(): void
    {
        foreach (['colors', 'sizes'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::dropIfExists('stock_movements');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn(['unit_price', 'line_total']);
        });

        Schema::dropIfExists('invoices');

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['manufacturer_id']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['supplier', 'purchase_order']);
            $table->renameColumn('cost_price', 'single_price');
        });

        Schema::table('statuses', fn (Blueprint $table) => $table->dropColumn('is_sellable'));
        Schema::table('items', fn (Blueprint $table) => $table->dropColumn(['is_prescription_only', 'is_controlled']));
    }
};
