<?php

use App\Enums\ImagePermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->boolean('object_managed')->default(false);
            $table->char('sha256', 64)->nullable();
            $table->char('idempotency_key', 64)->nullable();
            $table->char('request_fingerprint', 64)->nullable();
            $table->char('share_version', 32)->default('0');
            $table->timestamp('thumbnail_pending_at')->nullable()->index();
            $table->unsignedSmallInteger('thumbnail_attempts')->default(0);
            $table->index(['strategy_id', 'path', 'name']);
            $table->index(['user_id', 'created_at']);
            $table->index(['uploaded_ip', 'created_at']);
            $table->index(['permission', 'is_unhealthy', 'created_at']);
        });

        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX images_idempotency_key_unique ON images(idempotency_key) WHERE idempotency_key IS NOT NULL');
        } else {
            Schema::table('images', fn (Blueprint $table) => $table->unique('idempotency_key'));
        }

        // Legacy "private" links were anonymously readable and outside the gallery.
        DB::table('images')->where('permission', ImagePermission::Private)->update([
            'permission' => ImagePermission::LinkOnly,
        ]);

        DB::table('users')->select(['id', 'configs'])->chunkById(500, function ($users) {
            foreach ($users as $user) {
                $configs = json_decode($user->configs ?? '{}', true);
                if (is_array($configs) && (string) ($configs['default_permission'] ?? '0') === '0') {
                    $configs['default_permission'] = ImagePermission::LinkOnly;
                    DB::table('users')->where('id', $user->id)->update([
                        'configs' => json_encode($configs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        if (DB::table('images')->where('object_managed', true)->exists()) {
            throw new LogicException('Restore a coordinated database and media backup to roll back after accepting new uploads.');
        }

        DB::table('images')->where('permission', ImagePermission::LinkOnly)->update([
            'permission' => ImagePermission::Private,
        ]);

        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('DROP INDEX images_idempotency_key_unique ON images');
        } else {
            Schema::table('images', fn (Blueprint $table) => $table->dropUnique(['idempotency_key']));
        }

        Schema::table('images', function (Blueprint $table) {
            $table->dropIndex(['strategy_id', 'path', 'name']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['uploaded_ip', 'created_at']);
            $table->dropIndex(['permission', 'is_unhealthy', 'created_at']);
            $table->dropIndex(['thumbnail_pending_at']);
            $table->dropColumn(['object_managed', 'sha256', 'idempotency_key', 'request_fingerprint', 'share_version',
                'thumbnail_pending_at', 'thumbnail_attempts']);
        });
    }
};
