<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('production_order_number')->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained();
            $table->foreignId('product_variant_id')->constrained();
            $table->integer('qty_ordered');
            $table->integer('qty_completed')->default(0);
            $table->enum('status', [
                'pending', 'materials_ready', 'in_progress', 'submitted_qc',
                'qc_passed', 'qc_failed', 'rework', 'completed', 'cancelled',
            ])->default('pending');
            $table->date('due_date')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->string('custom_spec_url')->nullable();
            $table->text('custom_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
