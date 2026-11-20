<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class StrategyConfig implements CastsAttributes
{
    public const SECRET_FIELDS = [
        'access_key', 'access_key_id', 'access_key_secret', 'secret_access_key', 'secret_key', 'secret_id',
        'password', 'private_key', 'passphrase',
    ];

    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $configs = json_decode($value ?? '{}', true, flags: JSON_THROW_ON_ERROR);
        foreach (self::SECRET_FIELDS as $name) {
            if (isset($configs[$name]['encrypted'])) {
                $configs[$name] = Crypt::decryptString($configs[$name]['encrypted']);
            }
        }

        return collect($configs);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return self::protect(collect($value)->all());
    }

    public static function protect(array $configs): string
    {
        foreach (self::SECRET_FIELDS as $name) {
            if (is_scalar($configs[$name] ?? null) && (string) $configs[$name] !== '') {
                $configs[$name] = ['encrypted' => Crypt::encryptString((string) $configs[$name])];
            }
        }

        return json_encode($configs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
