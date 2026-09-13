<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku_prefix')->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->boolean('is_customizable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('moq')->default(1);
            $table->integer('lead_time_days')->nullable();
            $table->integer('weight_grams')->nullable();
            $table->string('hs_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
