<?php

use App\Enums\ConfigKey;
use App\Support\SecretValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->convert(true);
    }

    public function down(): void
    {
        $this->convert(false);
    }

    private function convert(bool $encrypt): void
    {
        $record = DB::table('configs')->where('name', ConfigKey::Mail);
        $raw = $record->value('value');
        if ($raw === null) {
            return;
        }

        $mail = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        $path = 'mailers.smtp.password';
        $password = Arr::get($mail, $path);
        if (($encrypt && is_string($password) && $password !== '') || (! $encrypt && is_array($password))) {
            Arr::set($mail, $path, $encrypt ? SecretValue::encrypt($password) : SecretValue::decrypt($password));
            $record->update(['value' => json_encode($mail, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        }
        Cache::forget('configs');
    }
};
