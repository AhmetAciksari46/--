<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use App\Models\ManagerProfile;
use App\Models\TeacherProfile;
use App\Models\SchoolStudentProfile;

class EnsureSchoolExistsAndBelongsToUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // <-- MUST be first line
        if ($user->role === 'admin') {
            return $next($request);
        }


        $school = $request->route('school'); // model binding School instance

        // 1) Okul var mı?
        if (!$school) {
            return response()->json([
                'message' => 'Geçersiz okul bilgisi.'
            ], 403);
        }

        // Kullanıcının profilleri
        $managerProfile  = $user->managerProfile;
        $teacherProfile  = $user->teacherProfile;
        $studentProfile  = $user->schoolStudentProfile;

        $hasAccess = false;

        // 2) Manager mı ve okul aynı mı?
        if ($managerProfile && $managerProfile->school_id === $school->id) {
            $hasAccess = true;
        }

        // 3) Teacher mı?
        if ($teacherProfile && $teacherProfile->school_id === $school->id) {
            $hasAccess = true;
        }

        // 4) Öğrenci mi?
        if ($studentProfile && $studentProfile->school_id === $school->id) {
            $hasAccess = true;
        }

        // Erişim yoksa
        if (!$hasAccess) {
            return response()->json([
                'message' => 'Bu okula erişim yetkiniz bulunmamaktadır.'
            ], 403);
        }

        return $next($request);
    }
}
