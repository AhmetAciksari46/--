<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Profile\TeacherUpdateProfileSettingRequest;

/**
 * @OA\Tag(
 *     name="TeacherProfileSettings",
 *     description="Öğretmen profil ayarları"
 * )
 */
class TeacherProfileController extends Controller
{
    use ApiResponser;
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

    /**
     * @OA\Get(
     *     path="/api/me/teacher/getprofilesettings",
     *     tags={"TeacherProfileSettings"},
     *     summary="Öğretmen profil ayarlarını getir",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil ayarları bulundu",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherProfile")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
     *     @OA\Response(response=404, description="Profil bulunamadı")
     * )
     */

    public function getprofilesettings(Request $request)
    {
        $user = Auth::user();
        $profile = TeacherProfile::where('user_id', $user->id)
            ->with('user')
            ->firstOrFail();

        return $this->successResponse($profile);
    }
    /**
     * @OA\Put(
     *     path="/api/me/teacher/updateprofilesettings",
     *     tags={"TeacherProfileSettings"},
     *     summary="Öğretmen profil ayarlarını güncelle",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TeacherUpdateProfileSettingRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil güncellendi",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherProfile")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function updateprofilesettings(TeacherUpdateProfileSettingRequest $request)
    {
        $user = Auth::user();
        $profile = TeacherProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['payment_reminder' => false]
        );
        $profile->fill($request->validated());
        $profile->save();
        return $this->successResponse($profile->fresh(), __('api.profile_fetched'));
    }

    public function show($user_id) {}
}
