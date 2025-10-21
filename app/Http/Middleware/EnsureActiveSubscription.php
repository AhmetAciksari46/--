<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;

class EnsureActiveSubscription
{

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Kullanıcının aktif paketi var mı?
        $activeSubscription = Subscription::where('subscribable_type', get_class($user))
            ->where('subscribable_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->first();

        if (!$activeSubscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bu işlemi gerçekleştirmek için aktif bir paketiniz olmalı.'
            ], 403);
        }

        return $next($request);
    }
}
