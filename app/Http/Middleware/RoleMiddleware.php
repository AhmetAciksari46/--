<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next, string $role): Response
    {
        // Kullanıcının giriş yapmış olması gerekiyor
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Yetkisiz erişim.'], 401);
        }
        // Kullanıcının role alanı kontrol ediliyor
        if (trim(strtolower($user->role)) !== strtolower($role)) {
    return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
}

        return $next($request);
    }
}
