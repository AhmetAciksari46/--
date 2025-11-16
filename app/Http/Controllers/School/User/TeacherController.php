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
use App\Http\Requests\Teacher\TeacherUpdateRequestManager;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Support\Facades\DB;
use App\Models\School;

/**
 * @OA\Tag(
 *     name="Manager - Teacher İşlemleri",
 *     description="Öğretmenlerin kendi profil işlemleri"
 * )
 */
class TeacherController extends Controller
{
    use ApiResponser;




    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers",
     *     summary="Belirli bir okulun öğretmenlerini listeler",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of teachers")
     * )
     */
    public function index(School $school)
    {

        $teachers = User::where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school->id))
            ->with('teacherProfile')
            ->get();
        return $this->successResponse($teachers, 'Okula ait öğretmenler getirildi.', 200);
    }
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni getirir",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher details")
     * )
     */
    public function show(School $school, User $teacher)
    {
        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id != $school->id) {

            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        $teacher->load('teacherProfile');
        return $this->successResponse($teacher, 'Öğretmen bilgileri getirildi.', 200);
    }
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/teachers",
     *     summary="Belirli okula yeni öğretmen ekler",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","userName","password","branch_id"},
     *             @OA\Property(property="name", type="string", example="Ali Yılmaz"),
     *             @OA\Property(property="userName", type="string", example="aliyilmaz"),
     *             @OA\Property(property="email", type="string", example="ali@example.com"),
     *             @OA\Property(property="password", type="string", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", example="12345678"),
     *             @OA\Property(property="branch_id", type="integer", example=2),
     *             @OA\Property(property="phone", type="string", example="+905551234567"),
     *             @OA\Property(property="address", type="string", example="İstanbul"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1985-05-10"),
     *             @OA\Property(property="referance", type="string", example="REF001")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Teacher created successfully")
     * )
     */
    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'userName' => 'required|string|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
            'referance' => 'nullable|string|max:255',
        ]);

        $teacher = DB::transaction(function () use ($data, $school) {
            $user = User::create([
                'name' => $data['name'],
                'userName' => $data['userName'],
                'email' => $data['email'] ?? null,
                'password' => bcrypt($data['password']),
                'role' => 'teacher',
            ]);
            $user->assignRole('teacher');
            $profile = $user->teacherProfile()->create([
                'school_id' => $school->id,
                'branch_id' => $data['branch_id'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            return $user->load('teacherProfile');
        });

        return $this->successResponse($teacher, 'Yeni öğretmen oluşturuldu.', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni günceller",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen User ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TeacherUpdateRequestManager")
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher updated successfully")
     * )
     */

    public function update(TeacherUpdateRequestManager $request, School $school, User $teacher)
    {


        // 🔒 Öğretmen bu okula mı ait?
        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id != $school->id) {

            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        $validated = $request->validated();

        // USER alanları
        $userFields = array_filter(
            $validated,
            fn($k) =>
            in_array($k, ['name', 'email']),
            ARRAY_FILTER_USE_KEY
        );

        // TEACHER PROFILE alanları
        $profileFields = array_filter(
            $validated,
            fn($k) =>
            !in_array($k, ['name', 'email']),
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($userFields)) {
            $teacher->update($userFields);
        }

        if (!empty($profileFields)) {
            $teacher->teacherProfile->update($profileFields);
        }


        return $this->successResponse($teacher->load('teacherProfile'), 'Öğretmen başarıyla güncellendi.', 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/teachers/{teacher}",
     *     summary="Belirli öğretmeni siler",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="Öğretmen ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Teacher deleted")
     * )
     */
    public function destroy(School $school, User $teacher)
    {

        if ($teacher->teacherProfile->school_id != $school->id) {
            return $this->errorResponse('Bu öğretmen bu okula ait değil.', 403);
        }

        $teacher->delete();
        return $this->successResponse(null, 'Öğretmen Silindi', 200);
    }








    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Öğretmenin tüm izinlerini listeler",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response=200, description="Permissions list")
     * )
     */
    public function getPermissions(School $school, User $teacher)
    {

        return response()->json([
            'permissions' => $teacher->getAllPermissions()->pluck('name')
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Öğretmenin izinlerini günceller",
     *     tags={"Manager - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="attendance.edit")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permissions updated")
     * )
     */
    public function updatePermissions(Request $request, School $school, User $teacher)
    {
        // 🔥 Okul - öğretmen doğrulaması
        if (!$teacher->teacherProfile || $teacher->teacherProfile->school_id !== $school->id) {
            return response()->json(['message' => 'Bu öğretmen bu okula ait değil.'], 403);
        }

        $data = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $teacher->syncPermissions($data['permissions']);

        return response()->json([
            'message' => 'Teacher permissions updated successfully.',
            'new_permissions' => $teacher->getPermissionNames(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}/available-permissions",
     *     summary="Öğretmenlere atanabilir tüm izinleri döner",
     *     tags={"Manager - Teacher Permissions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tüm teacher permission listesi",
     *         @OA\JsonContent(
     *             @OA\Property(property="permissions", type="array",
     *                 @OA\Items(type="string", example="classmodel.view")
     *             )
     *         )
     *     )
     * )
     */
    public function availablePermissionsForTeachers(School $school, User $teacher)
    {
        return $this->authorize('manageTeacherPermissions', [$school, $teacher]);

        // Sadece öğretmen rolüne ait izinleri getir
        $teacherRole = \Spatie\Permission\Models\Role::where('name', 'teacher')->first();

        $permissions = $teacherRole
            ? $teacherRole->permissions->pluck('name')
            : collect([]);

        return response()->json([
            'available_permissions' => $permissions
        ]);
    }




    // /**
    //  * @OA\Put(
    //  *     path="/api/me/teacher/updateprofile",
    //  *     tags={"Manager - Teacher İşlemleri"},
    //  *     summary="Öğretmen kendi kullanıcı hesabını günceller",
    //  *     security={{"bearerAuth": {}}},
    //  *     @OA\RequestBody(
    //  *         required=true,
    //  *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Profil bilgileri başarıyla güncellendi",
    //  *         @OA\JsonContent(ref="#/components/schemas/User")
    //  *     ),
    //  *     @OA\Response(response=401, description="Yetkisiz erişim"),
    //  *     @OA\Response(response=422, description="Doğrulama hatası")
    //  * )
    //  */
    // public function update(UpdateProfileRequest $request)
    // {
    //     $user = $request->user();
    //     $validated = $request->validated();

    //     $updatedFields = [];
    //     if (isset($validated['name']) && $user->name !== $validated['name']) {
    //         $user->name = $validated['name'];
    //         $updatedFields[] = 'İsim';
    //     }

    //     if (isset($validated['userName']) && $user->userName !== $validated['userName']) {
    //         $user->userName = $validated['userName'];
    //         $updatedFields[] = 'kullanıcı adı';
    //     }

    //     if (isset($validated['email']) && $user->email !== $validated['email']) {
    //         $user->email = $validated['email'];
    //         $user->email_verified_at = null;
    //         $updatedFields[] = 'e-posta adresi';
    //     }

    //     if (!empty($validated['new_password'])) {
    //         $user->password = $validated['new_password']; // Hashed cast sayesinde otomatik hashlenir
    //         $updatedFields[] = 'şifre';
    //     }

    //     $user->save();

    //     if (in_array('e-posta adresi', $updatedFields)) {
    //         $user->sendEmailVerificationNotification();
    //     }

    //     if (empty($updatedFields)) {
    //         $message = 'Herhangi bir değişiklik yapılmadı.';
    //     } elseif (count($updatedFields) === 1) {
    //         $message = ucfirst($updatedFields[0]) . ' başarıyla güncellendi.';
    //     } else {
    //         $last = array_pop($updatedFields);
    //         $message = ucfirst(implode(', ', $updatedFields)) . ' ve ' . $last . ' başarıyla güncellendi.';
    //     }

    //     return $this->successResponse($user->fresh(), $message);
    // }

    // public function updatebyid(Request $request, $teacherId) //for admin
    // {}
    // public function updateprofilesettingsbyid(Request $request, $teacherId) //for admin
    // {}
}
