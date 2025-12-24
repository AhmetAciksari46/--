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
use App\Models\User;


/**
 * @OA\Tag(
 *     name="ManagerProfile",
 *     description="Manager profil bilgileri yönetimi (ManagerProfile tablosu)"
 * )
 */



class ManagerProfileController extends Controller
{

    use ApiResponser;


    /**
     * @OA\Put(
     *     path="/api/manager/profile/me",
     *     tags={"ManagerProfile"},
     *     summary="(Manager) Kendi profilini güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *             @OA\Property(property="note", type="string", example="Not"),
     *             @OA\Property(property="referance", type="string", example="REF001"),
     *             @OA\Property(property="school_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="payment_reminder", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil güncellendi"),
     *     @OA\Response(response=404, description="Profil bulunamadı"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function updateManagerProfile(Request $request)
    {

        $user = Auth::user(); // role: manager
        if (!$user->isManager()) {
            return $this->errorResponse('sadece managerlar istek atabilir.', 404);
        }



        $profile = ManagerProfile::firstOrCreate(['user_id' => $user->id]);
        if (!$profile) {
            return $this->errorResponse('Profil bulunamadı.', 404);
        }
        $validated = $request->validate([
            'phone'            => ['sometimes', 'string', 'max:20'],
            'address'          => ['sometimes', 'string', 'max:255'],
            'birth_date'       => ['sometimes', 'date', 'before:today'],
            'note'             => ['sometimes', 'string', 'max:500'],
            'referance'        => ['sometimes', 'string', 'max:255'],
            'school_id'        => ['sometimes', 'integer', 'exists:schools,id'],
            'payment_reminder' => ['sometimes', 'boolean'],
        ]);

        $profile->update($validated);

        return $this->successResponse($profile->load('user'), 'Profiliniz başarıyla güncellendi.');
    }

    /**
     * =========================
     *  ADMIN: GET BY ID & UPDATE BY ID
     * =========================
     */



    /**
     * @OA\Put(
     *     path="/api/admin/manager/{user_id}/profile",
     *     tags={"ManagerProfile"},
     *     summary="(Admin) Manager profilini user_id ile güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *             @OA\Property(property="note", type="string", example="Not"),
     *             @OA\Property(property="referance", type="string", example="REF001"),
     *             @OA\Property(property="school_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="payment_reminder", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil güncellendi"),
     *     @OA\Response(response=404, description="Profil bulunamadı"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function updateManagerProfileById(Request $request, int $user_id)
    {
        if (!auth()->user()->can('manager.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $profile = ManagerProfile::firstOrCreate(['user_id' => $user_id]);
            $validated = $request->validate(
                [
                    'phone'            => ['nullable', 'string', 'max:20'],
                    'address'          => ['nullable', 'string', 'max:255'],
                    'birth_date'       => ['nullable', 'date', 'before:today'],
                    'note'             => ['nullable', 'string', 'max:500'],
                    'referance'        => ['nullable', 'string', 'max:255'],
                    'school_id'        => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
                    'payment_reminder' => ['sometimes', 'boolean'],
                ]
            );

            $profile->update($validated);

            return $this->successResponse($profile->load('user'), 'Manager profili admin tarafından güncellendi.');
        } catch (\Exception $e) {
            return $this->errorResponse("Manager profile güncellenirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/manager/profile",
     *     tags={"ManagerProfile"},
     *     summary="(Manager) Kendi profilini oluştur (ilk kez kaydediyorsa)",
     *     description="Manager kendi profiline ait bilgileri kaydeder. Eğer profil zaten varsa hata döner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *             @OA\Property(property="note", type="string", example="Yönetici notu"),
     *             @OA\Property(property="referance", type="string", example="REF001"),
     *             @OA\Property(property="school_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="payment_reminder", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Profil başarıyla oluşturuldu"),
     *     @OA\Response(response=409, description="Profil zaten mevcut"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function storeManagerProfile(Request $request)
    {
        if (!auth()->user()->can('manager.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $user = Auth::user();

        try {

            if (!$user->hasRole('manager')) {
                return $this->errorResponse('Bu işlemi sadece manager kullanıcıları yapabilir.', 403);
            }

            if (ManagerProfile::where('user_id', $user->id)->exists()) {
                return $this->errorResponse('Bu kullanıcıya ait profil zaten mevcut.', 409);
            }

            $validated = $request->validate([
                'phone'            => ['nullable', 'string', 'max:20'],
                'address'          => ['nullable', 'string', 'max:255'],
                'birth_date'       => ['nullable', 'date', 'before:today'],
                'note'             => ['nullable', 'string', 'max:500'],
                'referance'        => ['nullable', 'string', 'max:255'],
                'school_id'        => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
                'payment_reminder' => ['sometimes', 'boolean'],
            ]);

            $profile = ManagerProfile::create(array_merge($validated, [
                'user_id' => $user->id,
            ]));

            return $this->successResponse($profile->load('user'), 'Profil başarıyla oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse("Ders oturumları getirilirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }

    /**
     * =========================
     *  ADMIN: STORE BY ID
     * =========================
     */

    /**
     * @OA\Post(
     *     path="/api/admin/manager/{user_id}/profile",
     *     tags={"ManagerProfile"},
     *     summary="(Admin) Belirli bir manager için profil oluştur",
     *     description="Admin, user_id'si verilen manager için profil oluşturabilir. Eğer zaten varsa hata döner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *             @OA\Property(property="note", type="string", example="Not"),
     *             @OA\Property(property="referance", type="string", example="REF001"),
     *             @OA\Property(property="school_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="payment_reminder", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Profil başarıyla oluşturuldu"),
     *     @OA\Response(response=409, description="Profil zaten mevcut"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function storeManagerProfileById(Request $request, $user_id)
    {
        if (!auth()->user()->can('manager.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }


        try {

            $targetUser = User::findOrFail($user_id);

            if ($targetUser->role !== 'manager') {
                return $this->errorResponse('Seçilen kullanıcı bir manager değildir.', 422);
            }

            if (ManagerProfile::where('user_id', $user_id)->exists()) {
                return $this->errorResponse('Bu kullanıcıya ait profil zaten mevcut.', 409);
            }

            $validated = $request->validate([
                'phone'            => ['nullable', 'string', 'max:20'],
                'address'          => ['nullable', 'string', 'max:255'],
                'birth_date'       => ['nullable', 'date', 'before:today'],
                'note'             => ['nullable', 'string', 'max:500'],
                'referance'        => ['nullable', 'string', 'max:255'],
                'school_id'        => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
                'payment_reminder' => ['sometimes', 'boolean'],
            ]);

            $profile = ManagerProfile::create(array_merge($validated, [
                'user_id' => $user_id,
            ]));

            return $this->successResponse($profile->load('user'), 'Manager profili admin tarafından oluşturuldu.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse("Ders oturumları getirilirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }
}
