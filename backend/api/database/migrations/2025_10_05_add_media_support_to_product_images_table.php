<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'video'])
                ->default('image')
                ->after('id');
            
            $table->string('thumbnail_path')->nullable()->change();
            
            $table->integer('file_size')
                ->nullable()
                ->after('thumbnail_path')
                ->comment('File size in KB');
            
            $table->integer('duration')
                ->nullable()
                ->after('file_size')
                ->comment('Video duration in seconds');
            
            $table->index(['product_id', 'media_type']);
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'file_size', 'duration']);
            $table->dropIndex(['product_id', 'media_type']);
        });
    }
};