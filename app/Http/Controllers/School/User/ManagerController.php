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

/**
 * @OA\Tag(
 *     name="ManagerUser",
 *     description="Manager kullanıcı bilgileri yönetimi (User tablosu)"
 * )
 */
class ManagerController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/manager/me",
     *     tags={"ManagerUser"},
     *     summary="(Manager) Kendi user bilgilerini getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Bilgiler başarıyla alındı"),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı")
     * )
     */
    public function getManagerUser()
    {
        $user = Auth::user();

        if (!$user->hasRole('manager')) {
            return $this->errorResponse('Bu işlem sadece manager kullanıcıları içindir.', 403);
        }

        return $this->successResponse($user->load('managerProfile'), 'Bilgiler başarıyla getirildi.');
    }

    /**
     * @OA\Put(
     *     path="/api/manager/me",
     *     tags={"ManagerUser"},
     *     summary="(Manager) Kendi user bilgilerini güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Yeni Yönetici Adı"),
     *             @OA\Property(property="userName", type="string", example="manager1"),
     *             @OA\Property(property="email", type="string", example="manager@okul.com"),
     *             @OA\Property(property="password", type="string", example="123456"),
     *             @OA\Property(property="password_confirmation", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Güncelleme başarılı"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function updateManagerUser(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('manager')) {
            return $this->errorResponse('Bu işlem sadece manager kullanıcıları içindir.', 403);
        }

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'userName'  => 'sometimes|string|max:255|unique:users,userName,' . $user->id,
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'sometimes|string|min:6|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse($user, 'Bilgileriniz başarıyla güncellendi.');
    }

    /**
     * =========================
     *  ADMIN: GET BY ID & UPDATE BY ID
     * =========================
     */

    /**
     * @OA\Get(
     *     path="/api/admin/manager/{id}",
     *     tags={"ManagerUser"},
     *     summary="(Admin) Manager user bilgilerini user_id ile getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Bilgiler getirildi"),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı")
     * )
     */
    public function getManagerUserById($id)
    {
        $user = User::where('role', 'manager')->with('managerProfile')->find($id);

        if (!$user) {
            return $this->errorResponse('Manager bulunamadı.', 404);
        }

        return $this->successResponse($user, 'Manager bilgileri getirildi.');
    }

    /**
     * @OA\Put(
     *     path="/api/admin/manager/{id}",
     *     tags={"ManagerUser"},
     *     summary="(Admin) Manager user bilgilerini user_id ile güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Yeni Yönetici"),
     *             @OA\Property(property="userName", type="string", example="manager123"),
     *             @OA\Property(property="email", type="string", example="manager@okul.com"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Güncelleme başarılı"),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function updateManagerUserById(Request $request, $id)
    {
        $user = User::where('role', 'manager')->find($id);

        if (!$user) {
            return $this->errorResponse('Manager bulunamadı.', 404);
        }

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'userName'  => 'sometimes|string|max:255|unique:users,userName,' . $user->id,
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'sometimes|string|min:6|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse($user, 'Manager bilgileri admin tarafından güncellendi.');
    }
}
