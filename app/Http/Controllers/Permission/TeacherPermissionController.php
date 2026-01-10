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
 *     name="Manager - Teacher Permission Management",
 *     description="Manager tarafından öğretmenlerin permission yönetimi"
 * )
 */
class TeacherPermissionController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/teachers/assignable-permissions",
     *     summary="Manager'ın öğretmene atayabileceği permission listesini döner",
     *     tags={"Manager - Teacher Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Assignable permission listesi")
     * )
     */
    public function assignablePermissions()
    {
        $authUser = auth()->user();

        // 🔐 Manager yetkisi
        if (!$authUser->hasRole('manager')) {
            abort(403, 'Sadece manager işlem yapabilir.');
        }

        $permissions = Permission::query()
            ->where('is_assignable', true)
            ->where('assign_level', 'manager')
            ->pluck('name');

        // 🎯 Manager'ın atayabileceği permission'lar
        return $this->successResponse($permissions, "Aktarılabilir izinler listesi getirildi", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Manager tarafından öğretmene permission atanır",
     *     tags={"Manager - Teacher Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="class.create")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission atandı"),
     *     @OA\Response(response=403, description="Yetkisiz işlem"),
     *     @OA\Response(response=422, description="Geçersiz permission")
     * )
     */
    public function assign(Request $request, School $school, User $teacher)
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('manager')) {
            abort(403);
        }

        if (
            !$teacher->hasRole('teacher') ||
            !$teacher->teacherProfile ||
            $teacher->teacherProfile->school_id !== $school->id
        ) {
            abort(403, 'Bu öğretmen bu okula ait değil.');
        }

        $data = $request->validate([
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permissions = Permission::query()
            ->whereIn('name', $data['permissions'])
            ->where('is_assignable', true)
            ->where('assign_level', 'manager')
            ->get();

        foreach ($permissions as $permission) {
            if ($permission->is_default) {
                return $this->errorResponse('Default permission atanamaz.', 422);
            }

            $teacher->givePermissionTo($permission);
        }
        return $this->successResponse(null, "Başarıyla yeni yetki verildi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Manager tarafından öğretmenden permission kaldırılır",
     *     tags={"Manager - Teacher Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="teacher", in="path", required=true),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="class.create")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission kaldırıldı")
     * )
     */
    public function revoke(Request $request, School $school, User $teacher)
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('manager')) {
            abort(403);
        }

        if (
            !$teacher->hasRole('teacher') ||
            !$teacher->teacherProfile ||
            $teacher->teacherProfile->school_id !== $school->id
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

            if ($teacher->hasPermissionTo($permissionName)) {
                $teacher->revokePermissionTo($permissionName);
            }
        }

        return $this->successResponse(null, "Başarıyla yetki kaldırıldı.", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/{teacher}/permissions",
     *     summary="Öğretmenin sahip olduğu tüm permission'ları döner",
     *     tags={"Manager - Teacher Permission Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="teacher", in="path", required=true),
     *
     *     @OA\Response(response=200, description="Teacher permission listesi")
     * )
     */
    public function list(School $school, User $teacher)
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('manager')) {
            abort(403);
        }

        if (
            !$teacher->hasRole('teacher') ||
            !$teacher->teacherProfile ||
            $teacher->teacherProfile->school_id !== $school->id
        ) {
            abort(403);
        }
        return $this->successResponse([
            'permissions' => $teacher->getAllPermissions()->pluck('name'),
        ], "Öğretmenlerin yetki listesi getirildi.", 200);
    }
}
