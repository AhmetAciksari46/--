<?php

namespace App\Http\Controllers\Permission;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Manager & Teacher - Student Permission Management",
 *     description="Manager ve Teacher tarafından öğrencilerin permission yönetimi"
 * )
 */
class StudentPermissionController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/schools/students/assignable-permissions",
     *     summary="Manager veya Teacher'ın öğrenciye atayabileceği permission listesini döner",
     *     tags={"Manager & Teacher - Student Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Assignable permission listesi")
     * )
     */
    public function assignablePermissions()
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['manager', 'teacher'])) {
            abort(403, 'Bu işlemi yapmak için yetkiniz yok.');
        }
        $permissions = Permission::query()
            ->where('is_assignable', true)
            ->whereIn('assign_level', ['teacher', 'manager'])
            ->whereJsonContains('allowed_roles', 'student')
            ->pluck('name');
        // 🎯 Atanabilir permission'lar
        return $this->successResponse($permissions, "Aktarılabilir izinler listesi getirildi", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/students/{student}/permissions",
     *     summary="Manager veya Teacher tarafından öğrenciye permission atanır",
     *     tags={"Manager & Teacher - Student Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="student", in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="attendance.view")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission atandı"),
     *     @OA\Response(response=403, description="Yetkisiz işlem"),
     *     @OA\Response(response=422, description="Geçersiz permission")
     * )
     */
    public function assign(Request $request, School $school, User $student)
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['manager', 'teacher'])) {
            abort(403);
        }

        if (
            !$student->hasRole('student') ||
            !$student->studentProfile ||
            $student->studentProfile->school_id !== $school->id
        ) {
            abort(403, 'Bu öğrenci bu okula ait değil.');
        }

        $data = $request->validate([
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permissions = Permission::query()
            ->whereIn('name', $data['permissions'])
            ->where('is_assignable', true)
            ->whereIn('assign_level', ['teacher', 'manager'])
            ->whereJsonContains('allowed_roles', 'student')
            ->get();

        foreach ($permissions as $permission) {
            // ⛔ Default permission atanamaz
            if ($permission->is_default) {
                abort(422, 'Default permission atanamaz.');
            }

            $student->givePermissionTo($permission);
        }
        return $this->successResponse(null, "Başarıyla yeni yetki verildi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/students/{student}/permissions",
     *     summary="Manager veya Teacher tarafından öğrenciden permission kaldırılır",
     *     tags={"Manager & Teacher - Student Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="student", in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="attendance.view")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission kaldırıldı")
     * )
     */
    public function revoke(Request $request, School $school, User $student)
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['manager', 'teacher'])) {
            abort(403);
        }

        if (
            !$student->hasRole('student') ||
            !$student->studentProfile ||
            $student->studentProfile->school_id !== $school->id
        ) {
            abort(403);
        }

        $data = $request->validate([
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        foreach ($data['permissions'] as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            // ⛔ Default permission silinemez
            if ($permission->is_default) {
                abort(422, 'Default permission kaldırılamaz.');
            }

            if ($student->hasPermissionTo($permissionName)) {
                $student->revokePermissionTo($permissionName);
            }
        }
        return $this->successResponse(null, "Başarıyla yetki kaldırıldı.", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{student}/permissions",
     *     summary="Öğrencinin sahip olduğu tüm permission'ları döner",
     *     tags={"Manager & Teacher - Student Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Student permission listesi")
     * )
     */
    public function list(School $school, User $student)
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['manager', 'teacher'])) {
            abort(403);
        }

        if (
            !$student->hasRole('student') ||
            !$student->studentProfile ||
            $student->studentProfile->school_id !== $school->id
        ) {
            abort(403);
        }
        return $this->successResponse([
            'permissions' => $student->getAllPermissions()->pluck('name'),
        ], "Öğrencilerin yetki listesi getirildi.", 200);
    }
}
