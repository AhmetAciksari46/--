<?php

namespace App\Http\Controllers\School\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ApiResponser;
use App\Http\Requests\Profile\UpdateProfileRequest;

class ManagerController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Put(
     *     path="/api/me/manager/updateprofile",
     *     tags={"ManagerProfile"},
     *     summary="Yönetici kendi kullanıcı hesabını günceller",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil bilgileri başarıyla güncellendi",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Yetkisiz erişim"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $updatedFields = [];
        if (isset($validated['name']) && $user->name !== $validated['name']) {
            $user->name = $validated['name'];
            $updatedFields[] = 'İsim';
        }

        if (isset($validated['userName']) && $user->userName !== $validated['userName']) {
            $user->userName = $validated['userName'];
            $updatedFields[] = 'kullanıcı adı';
        }

        if (isset($validated['email']) && $user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
            $updatedFields[] = 'e-posta adresi';
        }

        if (!empty($validated['new_password'])) {
            $user->password = $validated['new_password']; // Hashed cast sayesinde otomatik hashlenir
            $updatedFields[] = 'şifre';
        }

        $user->save();

        if (in_array('e-posta adresi', $updatedFields)) {
            $user->sendEmailVerificationNotification();
        }

        if (empty($updatedFields)) {
            $message = 'Herhangi bir değişiklik yapılmadı.';
        } elseif (count($updatedFields) === 1) {
            $message = ucfirst($updatedFields[0]) . ' başarıyla güncellendi.';
        } else {
            $last = array_pop($updatedFields);
            $message = ucfirst(implode(', ', $updatedFields)) . ' ve ' . $last . ' başarıyla güncellendi.';
        }

        return $this->successResponse($user->fresh(), $message);
    }
    //getbyid gibi ->manager kullanacak

    public function updatebyid(Request $request, $managerId) //for admin
    {}
    //getbyid gibi ->manager kullanacak

    public function updateprofilesettingsbyid(Request $request, $managerId) //for admin
    {}


    //buraya teacher düzenle. teacher profile düzenle. class düzenle. student ve student profile düzenle. eklenecek!
}
