<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('sales_order_id')->constrained();
            $table->string('carrier');
            $table->string('tracking_no')->nullable();
            $table->enum('incoterm', ['FOB', 'CIF', 'EXW', 'DDP']);
            $table->string('hs_code')->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['preparing', 'ready', 'dispatched', 'in_transit', 'delivered'])->default('preparing');
            $table->timestamp('estimated_arrival')->nullable();
            $table->timestamp('actual_arrival')->nullable();
            $table->string('certificate_of_origin')->nullable();
            $table->string('fair_trade_doc')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
