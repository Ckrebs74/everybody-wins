<?php

// =====================================================
// MIGRATION 5: database/migrations/2024_01_01_000005_create_products_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained();
            
            // Basis Info
            $table->string('title');
            $table->text('description');
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->enum('condition', ['new', 'like_new', 'good', 'acceptable'])->default('new');
            
            // Preise
            $table->decimal('retail_price', 10, 2)->nullable(); // UVP
            $table->decimal('target_price', 10, 2); // Zielpreis des Verkäufers
            
            // Entscheidung bei Nicht-Erreichen
            $table->enum('decision_type', ['keep', 'give', 'pending'])->default('pending');
            
            // Status
            $table->enum('status', ['draft', 'active', 'scheduled', 'completed', 'cancelled'])->default('draft');
            
            // SEO
            $table->string('slug')->unique();
            
            // Images (JSON array of URLs)
            $table->json('images')->nullable();
            
            // Stats
            $table->integer('view_count')->default(0);
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('seller_id');
            $table->index('category_id');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};