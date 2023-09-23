<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Environment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsShowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $envCode): Response
    {
        $environment = Environment::where('env_code', $envCode)->first();
        $openTime = strtotime($environment->start_time);
        $closedTime = strtotime($environment->end_time);
        $currentTime = strtotime(date('Y-m-d H:i:s'));
        if (!($currentTime >= $openTime  && $currentTime <= $closedTime)) {
            return response()->json([
                'status' => false,
                'message' => 'Page is closed'
            ]);
        }
        return $next($request);
    }
}
