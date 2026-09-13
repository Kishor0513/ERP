<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');
            $table->enum('channel', ['email', 'whatsapp', 'phone', 'notes']);
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('contact_person')->nullable();
            $table->timestamps();

            $table->index(['loggable_type', 'loggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
