<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->after('item_id');
            $table->index('manufacturer_id');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['manufacturer_id']);
            $table->dropForeign(['manufacturer_id']);
            $table->dropColumn('manufacturer_id');

        });
    }
};
