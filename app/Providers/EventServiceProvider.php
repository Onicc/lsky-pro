<?php

namespace App\Providers;

use App\Listeners\RevokeTokensOnPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PasswordReset::class => [RevokeTokensOnPasswordReset::class],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];
}
