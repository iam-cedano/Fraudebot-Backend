<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            AuditLog::create([
                'actor_id' => $request->user()?->id,
                'action' => $request->route()?->getName() ?? $request->method().' '.$request->path(),
                'method' => $request->method(),
                'path' => $request->path(),
                'query_hash' => $request->filled('q') ? $this->hash((string) $request->input('q')) : null,
                'ip_hash' => $request->ip() ? $this->hash($request->ip()) : null,
                'status' => $response->getStatusCode(),
                'metadata' => [
                    'route_parameters' => collect($request->route()?->parameters() ?? [])
                        ->map(fn ($value) => is_object($value) && isset($value->id) ? $value->id : $value)
                        ->all(),
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to persist API audit log.', [
                'exception' => $exception::class,
                'path' => $request->path(),
            ]);
        }

        return $response;
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($value)), (string) config('app.key'));
    }
}
