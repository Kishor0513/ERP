<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_account_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wholesale_account_id')->constrained()->cascadeOnDelete();
            $table->enum('doc_type', ['reseller_certificate', 'tax_id', 'signed_agreement', 'other']);
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_account_docs');
    }
};
