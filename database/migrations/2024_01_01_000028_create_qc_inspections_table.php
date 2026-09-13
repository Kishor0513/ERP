<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained();
            $table->foreignId('artisan_id')->nullable()->constrained();
            $table->foreignId('inspector_id')->constrained('users');
            $table->enum('result', ['pass', 'fail', 'rework']);
            $table->string('defect_reason')->nullable();
            $table->json('defect_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('inspected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_inspections');
    }
};
