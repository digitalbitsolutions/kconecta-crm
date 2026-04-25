<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrchestratorKey
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('orchestrator.enabled', true)) {
            return response()->json([
                'message' => 'Orchestrator disabled.',
            ], 503);
        }

        $expectedKey = (string) config('orchestrator.api_key', '');
        if ($expectedKey === '') {
            return $next($request);
        }

        $providedKey = (string) $request->header('X-Orchestrator-Key', '');
        if ($providedKey === '') {
            $bearer = $request->bearerToken();
            $providedKey = is_string($bearer) ? $bearer : '';
        }

        if (! hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized orchestrator request.',
            ], 401);
        }

        return $next($request);
    }
}

