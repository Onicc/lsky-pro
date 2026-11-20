<?php

namespace App\Providers;

use App\Enums\ConfigKey;
use App\Models\Group;
use App\Support\Installation;
use App\Utils;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function (BladeView $view): void {
            if (! Installation::exists()) {
                $view->with('_is_notice', false);

                return;
            }

            $request = request();
            if (! $request->attributes->has('lsky.group')) {
                $user = $request->user();
                $group = $user?->group ?? Group::query()->where('is_guest', true)->first();
                $request->attributes->set('lsky.group', $group);
            }

            $view->with([
                '_group' => $request->attributes->get('lsky.group'),
                '_is_notice' => trim(strip_tags((string) Utils::config(ConfigKey::SiteNotice))) !== '',
            ]);
        });
    }
}
