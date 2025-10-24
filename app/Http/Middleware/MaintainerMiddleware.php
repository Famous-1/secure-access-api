<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintainerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin or Maintainer privileges required.'
            ], 403);
        }

        return $next($request);
    }
}

