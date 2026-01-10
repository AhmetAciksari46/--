<?php

namespace App\Http\Controllers\Permission;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponser;
use Spatie\Permission\PermissionRegistrar;

/**
 * @OA\Tag(
 *     name="Admin - User Permission Management",
 *     description="Admin tarafından kullanıcıların permission yönetimi"
 * )
 */
class UserPermissionController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/users/assignable-permissions",
     *     summary="Admin'in ilgili kullanıcıya atayabileceği permission listesini döner",
     *     tags={"Admin - User Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Assignable permission listesi")
     * )
     */
    public function assignablePermissions()
    {
        // 🔐 Sadece admin
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Sadece admin işlem yapabilir.');
        }
        $permissions = Permission::query()
            ->where('is_assignable', true)
            ->where(function ($q) {
                $q->where('assign_level', 'admin')
                    ->orWhere('assign_level', 'manager');
            })
            ->pluck('name');
        return $this->successResponse($permissions, "Aktarılabilir izinler listesi getirildi", 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/users/{user}/permissions",
     *     summary="Admin tarafından kullanıcıya permission atanır",
     *     tags={"Admin - User Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="school.update")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission eklendi")
     * )
     */
    public function assign(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return $this->errorResponse("Yetki hatası", 403);
        }

        $data = $request->validate([
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permissions = Permission::whereIn('name', $data['permissions'])
            ->where('is_assignable', true)
            ->whereIn('assign_level', ['admin', 'manager'])
            ->pluck('name');

        $user->givePermissionTo($permissions);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $user->refresh();

        return $this->successResponse(null, "Başarıyla yeni yetki verildi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/users/{user}/permissions",
     *     summary="Admin tarafından kullanıcıdan permission kaldırılır",
     *     tags={"Admin - User Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="school.update")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Permission kaldırıldı")
     * )
     */
    public function revoke(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return $this->errorResponse("Yetki hatası", 403);
        }

        $data = $request->validate([
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        foreach ($data['permissions'] as $permissionName) {
            // ✅ Guard'ı sanctum olan permission'ı bul
            $permission = Permission::findByName($permissionName, 'sanctum');

            // ✅ sadece direct permission ise kaldır
            if ($user->hasDirectPermission($permissionName)) {
                $user->revokePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->unsetRelation('permissions');
        $user->unsetRelation('roles');
        $user->refresh();
        cache()->forget("permission_snapshot_{$user->id}");

        return $this->successResponse([
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], "Başarıyla yetki kaldırıldı.", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/users/{user}/permissions",
     *     summary="Kullanıcının sahip olduğu tüm permissionları döner (default + assigned)",
     *     tags={"Admin - User Permission Management"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(response=200, description="User permission listesi")
     * )
     */
    public function list(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return $this->errorResponse("Yetki hatası", 403);
        }


        return $this->successResponse([
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], "Tüm üyelerin yetki listesi getirildi.", 200);
    }
}
