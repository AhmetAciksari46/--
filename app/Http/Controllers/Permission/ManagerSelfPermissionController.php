<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Requests\Permissions\ManagerSelfPermissionRequest;
use App\Services\Permission\ManagerSelfPermissionService;

/**
 * @OA\Tag(
 *     name="Manager - Self Permissions",
 *     description="Manager'ın sadece kendisine permission ekleyip kaldırması"
 * )
 */
class ManagerSelfPermissionController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/manager/self/permissions/assignable",
     *     summary="Manager'ın kendisine ekleyebileceği permission listesini döner",
     *     tags={"Manager - Self Permissions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Assignable permission listesi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Atanabilir yetkiler listelendi."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="teacher.view.detail")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function assignable(ManagerSelfPermissionService $service)
    {
        $user = auth()->user();

        if (!$user->hasRole('manager')) {
            return $this->errorResponse('Sadece manager bu işlemi yapabilir.', 403);
        }

        $permissions = $service->getAssignableForSelf($user);

        return $this->successResponse($permissions, 'Atanabilir yetkiler listelendi.');
    }

    /**
     * @OA\Post(
     *     path="/api/manager/self/permissions/assign",
     *     summary="Manager kendisine permission ekler",
     *     tags={"Manager - Self Permissions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ManagerSelfPermissionRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permission eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Yetkiler başarıyla eklendi.")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz işlem")
     * )
     */
    public function assign(
        ManagerSelfPermissionRequest $request,
        ManagerSelfPermissionService $service
    ) {
        $user = auth()->user();

        if (!$user->hasRole('manager')) {
            return $this->errorResponse('Sadece manager bu işlemi yapabilir.', 403);
        }

        $service->assignToSelf($user, $request->validated()['permissions']);

        return $this->successResponse(null, 'Yetkiler başarıyla eklendi.');
    }

    /**
     * @OA\Post(
     *     path="/api/manager/self/permissions/revoke",
     *     summary="Manager kendisinden permission kaldırır",
     *     tags={"Manager - Self Permissions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ManagerSelfPermissionRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permission kaldırıldı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Yetkiler başarıyla kaldırıldı.")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Yetkisiz işlem")
     * )
     */
    public function revoke(
        ManagerSelfPermissionRequest $request,
        ManagerSelfPermissionService $service
    ) {
        $user = auth()->user();

        if (!$user->hasRole('manager')) {
            return $this->errorResponse('Sadece manager bu işlemi yapabilir.', 403);
        }

        $service->revokeFromSelf($user, $request->validated()['permissions']);

        return $this->successResponse(null, 'Yetkiler başarıyla kaldırıldı.');
    }
}
