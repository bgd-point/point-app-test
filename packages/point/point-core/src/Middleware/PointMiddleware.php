<?php

namespace Point\Core\Middleware;

use Closure;

class PointMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        self::fixerRequest($request->all());

        return $next($request);
    }

    private static function fixerRequest($requests)
    {
        foreach ($requests as $key => $value) {
            if (is_string($value)) {
                $value = preg_replace('/\s+/', ' ', $value);
                $requests[$key] = trim($value);
            }

            if (is_array($value)) {
                $requests[$key] = self::fixerRequest($value);
            }
        }

        return $requests;
    }
}
