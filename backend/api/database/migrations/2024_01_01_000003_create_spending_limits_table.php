<?php

// =====================================================
// MIGRATION 3: database/migrations/2024_01_01_000003_create_spending_limits_table.php
// KRITISCH: 10€/Stunde Limit!
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('hour_slot'); // Stunden-Slot (z.B. 2024-01-01 14:00:00)
            $table->decimal('amount_spent', 10, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'hour_slot']);
            $table->index('hour_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_limits');
    }
};