<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'transfer:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60 segundos

        return $next($request);
    }
}

