<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next, ...$roles): Response
    // {
    //     foreach ($roles as $role) {
    //         $user = Auth::user()->role->name;
    //         if ($user == $role) {
    //             return $next($request);
    //         }
    //     }
    //     return response()->json(['error' => 'Unauthorized'], 403);
    // }
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Memeriksa apakah pengguna memiliki token yang valid
        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Memeriksa apakah peran pengguna sesuai dengan yang diizinkan
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role->name; // Anda perlu memastikan bahwa 'role' adalah relasi yang ada pada model User

        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
