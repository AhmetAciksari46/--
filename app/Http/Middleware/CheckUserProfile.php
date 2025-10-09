<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Helpers\UserProfileHelper;

class CheckUserProfile
{
    public function handle(Request $request, Closure $next)
    {
         $user = $request->user();

        if (!$user || !UserProfileHelper::profileExists($user)) {
            return response()->json([
                'message' => 'Profiliniz eksik. Lütfen önce profilinizi oluşturun.'
            ], 403); // API için JSON
        }

        return $next($request);
    }
}
