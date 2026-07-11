<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('Idempotency-Key')) {
            return response()->json([
                'error_code' => 'MISSING_IDEMPOTENCY_KEY',
                'message' => 'Idempotency-Key header is required'
            ], 400);
        }

        return $next($request);
    }
}
