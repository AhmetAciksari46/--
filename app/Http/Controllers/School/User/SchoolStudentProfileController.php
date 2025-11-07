<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use app\Models\SchoolStudentProfile;
use Illuminate\Support\Facades\Auth;

class SchoolStudentProfileController extends Controller
{
    // App\Http\Controllers\TeacherProfileController.php

    public function update(Request $request, $user_id)
    {
        $profile = SchoolStudentProfile::where('user_id', $user_id)->firstOrFail();

        // 1. Yetki Kontrolü
        if (Auth::user()->role === 'teacher' && Auth::id() !== $user_id) {
            return response()->json(['message' => 'Sadece kendi profilinizi güncelleyebilirsiniz.'], 403);
        }
        // 2. Validation
        $request->validate([
            'cv_path' => 'nullable|string',
            'certification_level' => 'nullable|string',
            // ...
        ]);
        $profile->update($request->only('cv_path', 'certification_level'));
        return response()->json($profile);
    }

    public function show($user_id)
    {
        // Sadece yetkili roller görebilir
        $this->authorizeRole(['admin', 'manager', 'teacher', 'student']);

        // studentProfile tablosu user_id'ye göre aranır
        $profile = SchoolStudentProfile::where('user_id', $user_id)
            ->with('user') // Profile ait user bilgilerini de yükle
            ->firstOrFail();

        // Ayrıca, öğrencinin atanmış olduğu sınıfı da yükleyebilirsiniz.
        // $profile->load('currentClass'); 

        return response()->json($profile);
    }
}
