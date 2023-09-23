<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->user()->role == 'MEMBER') {
            return $next($request);
        }
        return response()->json([
            'status' => false,
            'message' => "Unauthorized Access, User's role is not MEMBER"
        ]);
    }
}
