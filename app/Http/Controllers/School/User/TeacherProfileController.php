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

    /**
     * @OA\Put(
     *     path="/api/teacher/{user_id}/update",
     *     tags={"TeacherProfileSettings"},
     *     summary="Öğretmen profilini güncelle",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="Güncellenecek öğretmenin kullanıcı ID'si",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cv_path", type="string", description="Özgeçmiş yolu"),
     *             @OA\Property(property="certification_level", type="string", description="Sertifikasyon seviyesi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil başarıyla güncellendi",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherProfile")
     *     ),
     *     @OA\Response(response=403, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */


    public function update(Request $request, $user_id)
    {
        if (!auth()->user()->can('teacher.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $profile = TeacherProfile::where('user_id', $user_id)->firstOrFail();

        // 1. Yetki Kontrolü: 
        // Eğer istek atan kullanıcı (Auth::user()) ne Admin ne de Manager ise,
        // güncellenmeye çalışılan profilin kendi profili olduğundan emin ol!
        if (Auth::user()->role === 'teacher' && Auth::id() !== $user_id) {
            return $this->errorResponse('Sadece kendi profilinizi güncelleyebilirsiniz.', 403);
        }

        // 2. Validation
        $request->validate([
            'cv_path' => 'nullable|string',
            'certification_level' => 'nullable|string',
            // ...
        ]);

        // 3. Güncelleme
        $profile->update($request->only('cv_path', 'certification_level'));

        return $this->successResponse($profile, "Profil başarıyla güncellendi.", 200);
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
        if (!$user->isTeacher()) {
            return $this->errorResponse('Sadece teacherlar istek atabilir.', 404);
        }
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
        if (!$user->isTeacher()) {
            return $this->errorResponse('Sadece teacherlar istek atabilir.', 404);
        }
        $profile = TeacherProfile::firstOrCreate(
            ['user_id' => $user->id]
        );

        $profile->fill($request->validated());
        $profile->save();
        return $this->successResponse($profile->fresh(), __('api.profile_fetched'), 200);
    }
}
