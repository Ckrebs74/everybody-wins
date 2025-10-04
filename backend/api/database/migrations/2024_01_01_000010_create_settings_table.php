<?php

// =====================================================
// MIGRATION 10: database/migrations/2024_01_01_000010_create_settings_table.php
// =====================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'integer', 'float', 'boolean', 'json'])
                  ->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['group', 'key']);
        });

        // Initial Settings
        DB::table('settings')->insert([
            [
                'group' => 'limits',
                'key' => 'max_spending_per_hour',
                'value' => '10',
                'type' => 'float',
                'description' => 'Maximum spending per hour in EUR (Gambling regulation)'
            ],
            [
                'group' => 'limits',
                'key' => 'max_tickets_per_raffle',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Maximum tickets per user per raffle'
            ],
            [
                'group' => 'fees',
                'key' => 'platform_commission',
                'value' => '0.30',
                'type' => 'float',
                'description' => 'Platform commission (30%)'
            ],
            [
                'group' => 'features',
                'key' => 'min_age',
                'value' => '18',
                'type' => 'integer',
                'description' => 'Minimum age requirement'
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};