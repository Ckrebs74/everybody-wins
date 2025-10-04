<?php

// =====================================================
// MIGRATION 8: database/migrations/2024_01_01_000008_create_transactions_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            
            // Type & Amount
            $table->enum('type', [
                'deposit', 
                'withdrawal', 
                'ticket_purchase', 
                'winning', 
                'refund', 
                'bonus', 
                'fee'
            ]);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            
            // Reference
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                  ->default('pending');
            
            // Details
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};