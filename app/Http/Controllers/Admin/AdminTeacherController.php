<?php

namespace App\Http\Controllers\Admin;

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
use Spatie\Permission\Models\Role;


/**
 * @OA\Tag(
 *     name="Admin - Teacher İşlemleri",
 *     description="Admin tarafından öğretmen yönetimi işlemleri",
 * )
 */
class AdminTeacherController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/teachers",
     *     summary="Tüm Öğretmenleri listeler",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of teachers")
     * )
     */
    public function index()
    {

        $teachers = User::role('teacher')
            ->with('teacherProfile')
            ->get();
        return $this->successResponse($teachers, 'Okula ait öğretmenler getirildi.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/teachers/getteachersbyschoolid/{school}",
     *     summary="Belirli bir okulun öğretmenlerini listeler",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of teachers")
     * )
     */
    public function getbySchoolId(School $school)
    {
        if (!$school) {
            return $this->errorResponse('Okul bulunamadı.', 404);
        }
        $teachers = User::where('role', 'teacher')
            ->whereHas('teacherProfile', fn($q) => $q->where('school_id', $school->id))
            ->with('teacherProfile')
            ->get();
        if ($teachers->count() === 0) {
            return $this->successResponse([], 'Bu okula ait öğretmen bulunamadı.', 200);
        }
        return $this->successResponse($teachers, 'Okula ait öğretmenler getirildi.', 200);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/teachers/{teacher}",
     *     summary="Belirli öğretmeni getirir",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
    public function show(User $teacher)
    {
        if (!$teacher || $teacher->role !== 'teacher') {
            return $this->errorResponse('Öğretmen bulunamadı.', 404);
        }
        $teacher->load('teacherProfile');
        return $this->successResponse($teacher, 'Öğretmen bilgileri getirildi.', 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/teachers/",
     *     summary="Belirli okula yeni öğretmen ekler",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
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
     *             @OA\Property(property="school_id", type="integer", example=1),
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'userName' => 'required|string|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
            'referance' => 'nullable|string|max:255',
        ]);
        $schoolId = $request->school_id;
        $teacher = DB::transaction(function () use ($data, $schoolId) {
            $user = User::create([
                'name' => $data['name'],
                'userName' => $data['userName'],
                'email' => $data['email'] ?? null,
                'password' => bcrypt($data['password']),
                'role' => 'teacher',
            ]);
            $user->assignRole('teacher');
            $profile = $user->teacherProfile()->create([
                'school_id' => $schoolId,
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
     *     path="/api/admin/teachers/{teacher}",
     *     summary="Belirli öğretmeni günceller",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
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

    public function update(TeacherUpdateRequestManager $request, User $teacher)
    {

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
     *     path="/api/admin/teachers/{teacher}",
     *     summary="Belirli öğretmeni siler",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
    public function destroy(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            return $this->errorResponse('Silinecek kullanıcı bir öğretmen değil.', 400);
        }
        if (!$teacher || $teacher->role !== 'teacher') {
            return $this->errorResponse('Öğretmen bulunamadı.', 404);
        }
        $teacher->delete();
        return $this->successResponse(null, 'Öğretmen Silindi', 200);
    }




    /**
     * @OA\Get(
     *     path="/api/admin/teachers/{teacher}/permissions",
     *     summary="Öğretmenin tüm izinlerini listeler",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
    public function getPermissions(User $teacher)
    {
        return $this->successResponse($teacher->getAllPermissions()->pluck('name'), 'Öğretmenin izinleri getirildi.', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/teachers/{teacher}/permissions",
     *     summary="Öğretmenin izinlerini günceller",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
    public function updatePermissions(Request $request, User $teacher)
    {
        $data = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        if (empty($data['permissions'])) {
            return response()->json(['message' => 'En az 1 permission seçilmelidir.'], 422);
        }



        $teacher->syncPermissions($data['permissions']);
        return $this->successResponse(null, 'Öğretmen izinleri başarıyla güncellendi.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/teachers/available-permissions",
     *     summary="Öğretmenlere atanabilir tüm izinleri döner",
     *     tags={"Admin - Teacher İşlemleri"},
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
    public function availablePermissionsForTeachers()
    {
        $teacherRole = Role::where('name', 'teacher')->first();

        if (!$teacherRole) {
            return response()->json(['message' => 'Teacher rolü bulunamadı.'], 500);
        }

        $permissions = $teacherRole->permissions->pluck('name');

        return $this->successResponse([
            'available_permissions' => $permissions
        ], 'Teacher rolüne atanabilir izinler listelendi.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/teachers/{teacher}/permissions",
     *     summary="Öğretmenden belirli izinleri kaldırır",
     *     tags={"Admin - Teacher İşlemleri"},
     *     security={{"bearerAuth":{}}},
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
     *                 @OA\Items(type="string", example="student.view")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permissions removed successfully")
     * )
     */
    public function removePermissions(Request $request, User $teacher)
    {


        $data = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        foreach ($data['permissions'] as $permission) {
            if ($teacher->hasPermissionTo($permission)) {
                $teacher->revokePermissionTo($permission);
            }
        }

        return $this->successResponse(
            null,
            'Belirtilen izinler öğretmenden kaldırıldı.',
            200
        );
    }
}
