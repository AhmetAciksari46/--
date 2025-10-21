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
        $schoolId = $request->route('school')?->id ?? null;

        // Eğer kullanıcı manager veya teacher ise kendi schoolId'si ile kontrol
        // Eğer öğrenci ise öğrenci -> schoolId ile kontrol
        if (!$schoolId) {
            if ($user->role === 'manager' || $user->role === 'teacher') {
                $schoolId = $user->school_id ?? $user->manager_profile?->schoolId ?? null;
            } elseif ($user->role === 'schoolstudent' || $user->role === 'individualstudent') {
                $schoolId = $user->student_profile?->school_id ?? null;
            }
        }
        if (!$schoolId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Okul bilgisi bulunamadı.'
            ], 403);
        }

        $school = School::find($schoolId);

        if (!$school || !$school->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bu işlemi gerçekleştirmek için okulun aktif bir paketi olmalı.'
            ], 403);
        }

        return $next($request);
    }
}
