<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Http\Result;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    use Result;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && $user->status !== UserStatus::Normal) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? $this->fail('账号已停用。')->setStatusCode(403)
                : redirect()->route('login')->withErrors(['email' => __('auth.failed')]);
        }

        return $next($request);
    }
}
