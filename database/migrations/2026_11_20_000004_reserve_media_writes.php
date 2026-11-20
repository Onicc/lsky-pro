<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_writes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('strategy_id')->constrained('strategies')->restrictOnDelete();
            $table->string('image_key', 64)->unique();
            $table->string('path');
            $table->string('name');
            $table->decimal('size', 10, 2);
            $table->char('idempotency_key', 64)->nullable();
            $table->char('request_fingerprint', 64)->nullable();
            $table->boolean('write_attempted')->default(false);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX media_writes_idempotency_key_unique ON media_writes(idempotency_key) WHERE idempotency_key IS NOT NULL');
        } else {
            Schema::table('media_writes', fn (Blueprint $table) => $table->unique('idempotency_key'));
        }
    }

    public function down(): void
    {
        if (DB::table('media_writes')->exists()) {
            throw new LogicException('Wait for in-progress uploads and staged-file cleanup before rolling back.');
        }

        Schema::dropIfExists('media_writes');
    }
};
