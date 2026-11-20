<?php

namespace App\Http\Middleware;

use App\Enums\ConfigKey;
use App\Http\Result;
use App\Support\Installation;
use App\Utils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsInstalled
{
    use Result;

    public function handle(Request $request, Closure $next): Response
    {
        if (! Installation::exists()) {
            if ($request->expectsJson() || $request->is('api/*') || ! $request->isMethodSafe()) {
                return $this->fail('Lsky Pro 尚未初始化。')->setStatusCode(503);
            }

            return redirect()->route('install');
        }

        config(['app.name' => Utils::config(ConfigKey::AppName, config('app.name'))]);
        $mail = Utils::config(ConfigKey::Mail);
        if ($mail !== null) {
            $mailConfig = $mail->toArray();
            $smtp = &$mailConfig['mailers']['smtp'];
            $smtp['scheme'] = ($smtp['encryption'] ?? null) === 'ssl' ? 'smtps' : 'smtp';
            config(['mail' => array_replace_recursive(config('mail'), $mailConfig)]);
        }

        return $next($request);
    }
}
