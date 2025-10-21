<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagerProfile;
use App\Models\SchoolStudentProfile;
use App\Models\TeacherProfile;
use App\Traits\ApiResponser;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Profile\ManagerUpdateProfileSettingRequest;


/**
 * @OA\Tag(
 *     name="Manager Profile",
 *     description="Manager Profile İşlemleri",
 * )
 * @OAS\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="http",
 *      scheme="bearer"
 * )
 */

/**
 * ---------------------------
 * Swagger Schemas
 * ---------------------------
 */

/**
 * @OA\Schema(
 *     schema="ManagerUpdateProfileSettingRequest",
 *     type="object",
 *     title="Manager Update Profile Request",
 *     description="Yönetici profil ayarlarını güncelleme requesti",
 *     required={},
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01", nullable=true),
 *     @OA\Property(property="note", type="string", example="Özel not", nullable=true),
 *     @OA\Property(property="referance", type="string", example="ABC123", nullable=true),
 *     @OA\Property(property="schoolId", type="integer", example=2, nullable=true),
 *     @OA\Property(property="payment_reminder", type="boolean", example=true)
 * )
 */
/**
 * @OA\Schema(
 *     schema="SchoolStudentProfile",
 *     type="object",
 *     title="SchoolStudentProfile",
 *     description="Okul öğrencisi profil modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=10),
 *     @OA\Property(property="first_name", type="string", example="Ahmet"),
 *     @OA\Property(property="last_name", type="string", example="Açıksarı"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2010-05-01", nullable=true),
 *     @OA\Property(property="class", type="string", example="5/A", nullable=true),
 *     @OA\Property(property="notes", type="string", example="Özel not", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User",
 *         nullable=true
 *     )
 * )
 */
class ManagerProfileController extends Controller
{

    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/manager/profile/settings",
     *     tags={"ManagerProfile"},
     *     summary="Yönetici profil ayarlarını getir",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Profil ayarları bulundu", @OA\JsonContent(ref="#/components/schemas/ManagerProfile")),
     *     @OA\Response(response=404, description="Profil ayarları bulunamadı")
     * )
     */
    public function getprofilesettings(Request $request)
    {
        $user = Auth::user();
        $profile = ManagerProfile::where('user_id', $user->id)
            ->with('user')
            ->first();
        if (!$profile) {
            return $this->errorResponse('Profil Ayarları Bulunamadı', 404);
        }
        return $this->successResponse($profile);
        //return $this->successResponse($profile, __('api.profile_fetched'));

    }
    /**
     * @OA\Put(
     *     path="/manager/profile/settings",
     *     tags={"ManagerProfile"},
     *     summary="Yönetici profil ayarlarını güncelle",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ManagerUpdateProfileSettingRequest")
     *     ),
     *     @OA\Response(response=200, description="Profil güncellendi", @OA\JsonContent(ref="#/components/schemas/ManagerProfile")),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */

    public function updateprofilesettings(ManagerUpdateProfileSettingRequest $request)
    {
        $user = Auth::user();
        $profile = ManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['payment_reminder' => false]
        );
        $profile->fill($request->validated());
        $profile->save();
        return $this->successResponse($profile->fresh(), __('api.profile_fetched'));
    }



    /**
     * @OA\Get(
     *     path="/manager/profile/{user_id}",
     *     tags={"ManagerProfile"},
     *     summary="Belirli kullanıcıya ait öğrenci profilini getir",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Profil bulundu", @OA\JsonContent(ref="#/components/schemas/SchoolStudentProfile")),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Profil bulunamadı")
     * )
     */

    //getbyid gibi ->manager kullanacak
    public function show($user_id)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher', 'student']);
        $profile = SchoolStudentProfile::where('user_id', $user_id)
            ->with('user') // Profile ait user bilgilerini de yükle
            ->firstOrFail();
        return response()->json($profile);
    }
}
