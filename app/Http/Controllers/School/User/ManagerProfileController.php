<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;

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
 *     name="ManagerProfile",
 *     description="Yönetici profil yönetimi"
 * )
 * @OA\SecurityScheme(
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



class ManagerProfileController extends Controller
{

    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/me/manager/getprofilesettings",
     *     tags={"ManagerProfile"},
     *     summary="Yönetici profil ayarlarını getir",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil ayarları bulundu",
     *         @OA\JsonContent(ref="#/components/schemas/ManagerProfile")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
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
     *     path="/api/me/manager/updateprofilesettings",
     *     tags={"ManagerProfile"},
     *     summary="Yönetici profil ayarlarını güncelle",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ManagerUpdateProfileSettingRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil güncellendi",
     *         @OA\JsonContent(ref="#/components/schemas/ManagerProfile")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
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
     *     path="/api/manager/profile/{user_id}",
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
     *     @OA\Response(
     *         response=200,
     *         description="Profil bulundu",
     *         @OA\JsonContent(ref="#/components/schemas/SchoolStudentProfile")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
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
