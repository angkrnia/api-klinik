<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()->role;

        if (auth()->check() && $role === 'doctor' || $role === 'dokter') {
            return $next($request);
        }

        return response()->json([
            'code'      => 403,
            'status'    => false,
            'message'   => 'Only doctor can access this route.'
        ], 403);
    }
}
