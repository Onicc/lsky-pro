<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    public function hosts(): array
    {
        $hosts = config('lsky.trusted_hosts');
        if ($host = parse_url(config('app.url'), PHP_URL_HOST)) {
            $hosts[] = $host;
        }

        return array_map(fn (string $host): string => '^'.preg_quote($host, '/').'$', array_unique($hosts));
    }
}
