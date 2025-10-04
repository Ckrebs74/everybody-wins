<?php

// =====================================================
// MIGRATION 6: database/migrations/2024_01_01_000006_create_raffles_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            
            // Timing
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('drawn_at')->nullable();
            
            // Preise (inkl. 30% Provision)
            $table->decimal('target_price', 10, 2);
            $table->decimal('platform_fee', 10, 2); // 30% Provision
            $table->decimal('total_target', 10, 2); // target_price + platform_fee
            
            // Status
            $table->enum('status', ['scheduled', 'active', 'pending_draw', 'completed', 'cancelled', 'refunded'])
                  ->default('scheduled');
            $table->boolean('target_reached')->default(false);
            
            // Stats
            $table->integer('tickets_sold')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->integer('unique_participants')->default(0);
            
            // Winner
            $table->unsignedBigInteger('winner_ticket_id')->nullable();
            $table->dateTime('winner_notified_at')->nullable();
            $table->boolean('prize_claimed')->default(false);
            
            // Final Decision (wenn Ziel nicht erreicht)
            $table->enum('final_decision', ['product_to_winner', 'money_to_winner', 'money_to_seller'])
                  ->nullable();
            $table->decimal('payout_amount', 10, 2)->nullable();
            
            // Fairness
            $table->string('random_seed', 64)->nullable(); // Für nachvollziehbare Ziehung
            
            $table->timestamps();
            
            $table->index('status');
            $table->index(['starts_at', 'ends_at']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffles');
    }
};