<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

final class SecretValue
{
    public static function encrypt(?string $value): array|string|null
    {
        return $value === null || $value === '' ? $value : ['encrypted' => Crypt::encryptString($value)];
    }

    public static function decrypt(mixed $value): ?string
    {
        if (is_array($value) && is_string($value['encrypted'] ?? null)) {
            return Crypt::decryptString($value['encrypted']);
        }

        return is_scalar($value) ? (string) $value : null;
    }
}
