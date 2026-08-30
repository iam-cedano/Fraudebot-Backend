<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResponseIsJSON
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $scalarPath = trim((string) config('scalar.path', 'scalar'), '/');

        if ($scalarPath !== '' && $request->is($scalarPath, $scalarPath.'/*')) {
            return $next($request);
        }

        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
