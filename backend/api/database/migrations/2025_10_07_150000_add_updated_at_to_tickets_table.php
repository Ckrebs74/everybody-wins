<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // updated_at hinzufügen - purchased_at bleibt vorerst bestehen
            $table->timestamp('updated_at')->nullable()->after('purchased_at');
        });
        
        // Bestehende Tickets: updated_at = purchased_at setzen
        DB::statement('UPDATE tickets SET updated_at = purchased_at WHERE updated_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};