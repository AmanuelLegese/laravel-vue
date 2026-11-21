<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('generic_name')->nullable()->after('name');
            $table->string('strength', 50)->nullable()->after('generic_name');
            $table->string('dosage_form', 50)->nullable()->after('strength');
            $table->unsignedInteger('reorder_level')->default(0)->after('dosage_form');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropForeign(['size_id']);
            $table->dropColumn(['color_id', 'size_id']);
            $table->string('batch_number', 100)->nullable()->after('manufacturer_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('batch_number');
            $table->foreignId('color_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['generic_name', 'strength', 'dosage_form', 'reorder_level']);
        });
    }
};
