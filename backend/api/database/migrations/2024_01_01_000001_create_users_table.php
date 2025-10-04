<?php
// =====================================================
// MIGRATION 1: database/migrations/2024_01_01_000001_create_users_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('username', 100)->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['buyer', 'seller', 'both', 'admin'])->default('buyer');
            
            // KYC/Verification
            $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('age_verified')->default(false);
            $table->date('birth_date')->nullable();
            
            // Profile
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            
            // Address
            $table->string('street')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country_code', 2)->default('DE');
            
            // Status
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            
            // Timestamps
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indices
            $table->index('role');
            $table->index('kyc_status');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};