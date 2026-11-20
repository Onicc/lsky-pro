<?php

namespace App\Http\Middleware;

use App\Enums\ConfigKey;
use App\Enums\UserStatus;
use App\Http\Result;
use App\Utils;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeApiRequest
{
    use Result;

    public function handle(Request $request, Closure $next, string $mode, ?string $ability = null): Response
    {
        if ($mode === 'optional' && ! $request->headers->has('Authorization')) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();
        if ($user === null) {
            return $this->fail('Authentication failed.')->setStatusCode(401);
        }
        if ($user->status !== UserStatus::Normal || (Utils::config(ConfigKey::IsUserNeedVerify) && ! $user->hasVerifiedEmail())) {
            return $this->fail('Account is not permitted to use the API.')->setStatusCode(403);
        }
        if ($ability !== null && ! $user->tokenCan($ability)) {
            return $this->fail('Token does not have the required permission.')->setStatusCode(403);
        }

        Auth::shouldUse('sanctum');

        return $next($request);
    }
}
