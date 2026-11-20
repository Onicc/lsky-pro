<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->index(['created_at', 'id']);
            $table->index(['user_id', 'album_id', 'created_at', 'id'], 'images_user_album_created_idx');
        });
        Schema::table('users', fn (Blueprint $table) => $table->index('created_at'));
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->dropIndex(['created_at', 'id']);
            $table->dropIndex('images_user_album_created_idx');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex(['created_at']));
    }
};
