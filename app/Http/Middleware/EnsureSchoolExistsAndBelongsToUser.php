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


        $school = $request->route('school'); // URL'deki {school} parametresini alıyoruz
        // 1️⃣ Okul var mı kontrolü
        if (! $school) {
            return response()->json([
                'message' => 'Geçersiz okul bilgisi. Lütfen önce bir okul oluşturun.'
            ], 403);
        }
        $user = $request->user();

        $managerProfile = ManagerProfile::where('user_id', $user->id)->first();
        $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();
        $studentProfile = SchoolStudentProfile::where('user_id', $user->id)->first();
        $hasAccess = false;
        $profileType = null;


        if ($managerProfile && $managerProfile->school_id === $school->id) {
            $hasAccess = true;
            $profileType = 'manager';
        } elseif ($teacherProfile && $teacherProfile->school_id === $school->id) {
            $hasAccess = true;
            $profileType = 'teacher';
        } elseif ($studentProfile && $studentProfile->school_id === $school->id) {
            $hasAccess = true;
            $profileType = 'student';
        }

        // 4️⃣ Erişim kontrolü
        if (! $hasAccess) {
            return response()->json([
                'message' => 'Bu okula erişim yetkiniz bulunmamaktadır.'
            ], 403);
        }
        $request->attributes->set('school', $school);
        $request->attributes->set('profile_type', $profileType);

        return $next($request);
    }
}
