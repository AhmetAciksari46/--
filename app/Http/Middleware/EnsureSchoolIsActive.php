<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use App\Models\Subscription;

class EnsureSchoolIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Admin her okula erişebilir
        if ($user->role === 'admin') {
            return $next($request);
        }


        $school = $request->route('school');

        if (!$school || !$school->is_active) {
            return response()->json([
                'message' => 'Bu okulun aktif bir paketi yok.'
            ], 403);
        }

        return $next($request);
    }
}
