<?php

// =====================================================
// MIGRATION 7: database/migrations/2024_01_01_000007_create_tickets_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained();
            $table->foreignId('user_id')->constrained();
            
            // Ticket Info
            $table->string('ticket_number', 20)->unique();
            $table->decimal('price', 10, 2)->default(1.00);
            
            // Status
            $table->enum('status', ['valid', 'winner', 'loser', 'refunded'])->default('valid');
            $table->boolean('is_bonus_ticket')->default(false);
            
            // Timestamps
            $table->timestamp('purchased_at')->useCurrent();
            
            $table->index(['raffle_id', 'user_id']);
            $table->index('ticket_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};