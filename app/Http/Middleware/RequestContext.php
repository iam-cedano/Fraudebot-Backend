<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID', (string) Str::uuid());
        $request->attributes->set('request_id', $requestId);
        Log::withContext(['request_id' => $requestId]);

        $startedAt = hrtime(true);
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        Log::info('API request completed.', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            'user_id' => $request->user()?->id,
        ]);

        return $response;
    }
}
