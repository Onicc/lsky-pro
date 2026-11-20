<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_deletions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_id')->nullable()->constrained('strategies')->nullOnDelete();
            $table->string('image_key', 64)->unique();
            $table->string('path');
            $table->string('name');
            $table->boolean('object_managed');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_deletions');
    }
};
