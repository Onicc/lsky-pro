<?php

namespace App\Support;

final class Installation
{
    public static function marker(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function exists(): bool
    {
        return is_file(self::marker()) || is_file(base_path('installed.lock'));
    }
}
