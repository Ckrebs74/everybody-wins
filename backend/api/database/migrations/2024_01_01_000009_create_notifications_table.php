<?php

// =====================================================
// MIGRATION 9: database/migrations/2024_01_01_000009_create_notifications_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Content
            $table->string('type', 100);
            $table->string('title');
            $table->text('message');
            $table->string('action_url', 500)->nullable();
            
            // Status
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};