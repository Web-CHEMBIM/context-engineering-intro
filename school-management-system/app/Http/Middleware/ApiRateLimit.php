<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class ApiRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): BaseResponse
    {
        $key = $this->resolveRequestSignature($request, $limiter);
        
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts($limiter))) {
            return response()->json([
                'message' => 'Too Many Attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, $this->decaySeconds($limiter));

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $this->maxAttempts($limiter));
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $this->maxAttempts($limiter)));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds(RateLimiter::availableIn($key))->timestamp);

        return $response;
    }

    /**
     * Resolve the rate limiter key.
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        if ($request->user()) {
            return sprintf('%s:%s:%s', 
                $limiter, 
                $request->user()->id,
                $request->ip()
            );
        }

        return sprintf('%s:%s', $limiter, $request->ip());
    }

    /**
     * Get the maximum number of attempts for the given limiter.
     */
    protected function maxAttempts(string $limiter): int
    {
        return match ($limiter) {
            'auth' => 5,        // 5 attempts for authentication
            'api' => 60,        // 60 requests per minute for API
            'bulk' => 10,       // 10 requests per minute for bulk operations
            'reports' => 30,    // 30 requests per minute for reports
            default => 60,
        };
    }

    /**
     * Get the decay time in seconds for the given limiter.
     */
    protected function decaySeconds(string $limiter): int
    {
        return match ($limiter) {
            'auth' => 900,      // 15 minutes lockout for failed auth
            'api' => 60,        // 1 minute window
            'bulk' => 60,       // 1 minute window
            'reports' => 60,    // 1 minute window
            default => 60,
        };
    }
}