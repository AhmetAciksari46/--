<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagerProfileController extends Controller
{
    // App\Http\Controllers\TeacherProfileController.php

public function update(Request $request, $user_id)
{
    $profile = TeacherProfile::where('user_id', $user_id)->firstOrFail();

    // 1. Yetki Kontrolü: 
    // Eğer istek atan kullanıcı (Auth::user()) ne Admin ne de Manager ise,
    // güncellenmeye çalışılan profilin kendi profili olduğundan emin ol!
    if (Auth::user()->role === 'teacher' && Auth::id() !== $user_id) {
        return response()->json(['message' => 'Sadece kendi profilinizi güncelleyebilirsiniz.'], 403);
    }
    
    // 2. Validation
    $request->validate([
        'cv_path' => 'nullable|string',
        'certification_level' => 'nullable|string',
        // ...
    ]);

    // 3. Güncelleme
    $profile->update($request->only('cv_path', 'certification_level'));

    return response()->json($profile);
}
// App\Http\Controllers\StudentProfileController.php

public function show($user_id)
{
    // Sadece yetkili roller görebilir
    $this->authorizeRole(['admin', 'manager', 'teacher', 'student']);

    // studentProfile tablosu user_id'ye göre aranır
    $profile = StudentProfile::where('user_id', $user_id)
                 ->with('user') // Profile ait user bilgilerini de yükle
                 ->firstOrFail();

    // Ayrıca, öğrencinin atanmış olduğu sınıfı da yükleyebilirsiniz.
    // $profile->load('currentClass'); 

    return response()->json($profile);
}
}
