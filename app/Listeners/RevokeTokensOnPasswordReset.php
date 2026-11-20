<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class RevokeTokensOnPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        $event->user->tokens()->delete();
    }
}
