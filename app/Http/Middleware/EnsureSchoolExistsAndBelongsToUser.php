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
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

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


        if ($managerProfile && $managerProfile->schoolId === $school->id) {
            $hasAccess = true;
            $profileType = 'manager';
        } elseif ($teacherProfile && $teacherProfile->school_id === $school->id) {
            $hasAccess = true;
            $profileType = 'teacher';
        } elseif ($studentProfile && $studentProfile->schoolId === $school->id) {
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



        // $profile = ManagerProfile::where('user_id', $user->id)
        //     ->with('user')
        //     ->first();


        // if ($profile->schoolId !== $school->id) {
        //     return response()->json([
        //         'message' => 'Bu okula erişim yetkiniz bulunmamaktadır.'
        //     ], 403);
        // }

        // // 3️⃣ Okul bilgisini global olarak paylaşmak istersen:
        // $request->attributes->set('school', $school);

        // return $next($request);
    }
}
