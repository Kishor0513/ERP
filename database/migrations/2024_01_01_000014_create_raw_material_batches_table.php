<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_material_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->string('batch_no')->unique();
            $table->decimal('qty_received', 12, 4);
            $table->decimal('qty_remaining', 12, 4);
            $table->decimal('unit_cost', 12, 2);
            $table->timestamp('received_at');
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_batches');
    }
};
