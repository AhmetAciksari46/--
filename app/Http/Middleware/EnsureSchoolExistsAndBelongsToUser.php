<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use App\Models\ManagerProfile;

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

        // 2️⃣ Kullanıcı o okula mı bağlı?
        $user = $request->user();
        $profile = ManagerProfile::where('user_id', $user->id)
            ->with('user')
            ->first();


        if ($profile->schoolId !== $school->id) {
            return response()->json([
                'message' => 'Bu okula erişim yetkiniz bulunmamaktadır.'
            ], 403);
        }

        // 3️⃣ Okul bilgisini global olarak paylaşmak istersen:
        $request->attributes->set('school', $school);

        return $next($request);
    }
}
