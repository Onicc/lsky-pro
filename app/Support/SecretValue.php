<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

final class SecretValue
{
    private const PREFIX = 'enc:v1:';

    public static function encrypt(?string $value): ?string
    {
        return $value === null || $value === '' || self::isEncrypted($value)
            ? $value
            : self::PREFIX.Crypt::encryptString($value);
    }

    public static function decrypt(?string $value): ?string
    {
        return $value !== null && self::isEncrypted($value)
            ? Crypt::decryptString(substr($value, strlen(self::PREFIX)))
            : $value;
    }

    public static function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::PREFIX);
    }
}
