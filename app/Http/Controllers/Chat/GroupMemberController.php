<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\AddGroupMemberRequest;
use App\Http\Requests\Chat\UpdateGroupMemberRoleRequest;
use App\Models\Group;
use App\Models\GroupMember;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Chat Group Members",
 *     description="Sınıf sohbet grubu üyelerinin yönetimi"
 * )
 */
class GroupMemberController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/chat/groups/{group}/members",
     *     tags={"Chat Group Members"},
     *     summary="Sınıf sohbet grubu üyelerini göster id bazlı",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Grup Üyeleri Listelendi")
     * )
     */
    public function index(Group $group)
    {
        $this->authorizeClassroomGroup($group);


        $members = $group->members()->with('user')->get();
        return $this->successResponse($members, "Grup üyeleri getirildi", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/chat/groups/{group}/members",
     *     tags={"Chat Group Members"},
     *     summary="Add a new member to the classroom group",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AddGroupMemberRequest")),
     *     @OA\Response(response=201, description="Member added successfully")
     * )
     */
    public function store(AddGroupMemberRequest $request, Group $group)
    {

        $this->authorizeClassroomGroup($group);

        // Eğer zaten üye ise hata döner
        if ($group->members()->where('user_id', $request->user_id)->exists()) {
            return $this->errorResponse("İstenen üye zaten kayıtlı.", 404);
        }

        $member = GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'role_in_group' => $request->role_in_group,
        ]);
        return $this->successResponse($member, "Grup üyesi başarıyla oluşturuldu", 200);
    }


    /**
     * @OA\Put(
     *     path="/api/chat/groups/{group}/members/{member}",
     *     tags={"Chat Group Members"},
     *     summary="Üye Yetki Güncelleme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         required=true,
     *         description="grup üyesi ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateGroupMemberRoleRequest")),
     *     @OA\Response(response=200, description="Üye yetkisi güncellendi.")
     * )
     */
    public function update(UpdateGroupMemberRoleRequest $request, Group $group, GroupMember $member)
    {
        $this->authorizeClassroomGroup($group);

        if ($member->group_id !== $group->id) {
            return $this->errorResponse("İstenen üye bu gruba ait değildir.", 403);
        }

        $member->update(['role_in_group' => $request->role_in_group]);
        return $this->successResponse($member, "Grup üyesi başarıyla güncellendi.", 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/chat/groups/{group}/members/{member}",
     *     tags={"Chat Group Members"},
     *     summary="grupda ki bir üyeyi silme işlemi",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         required=true,
     *         description="grup üyesi ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Üye silindi."),
     *     @OA\Response(response=403, description="Bu işlemi yapmak için yetkiniz yok.")
     * )
     */
    public function destroy(Group $group, GroupMember $member)
    {
        $this->authorizeClassroomGroup($group);

        if ($member->group_id !== $group->id) {
            return $this->errorResponse("İstenen üye bu gruba ait değildir.", 403);
        }

        $member->delete();
        return $this->successResponse(null, "Grup üyesi başarıyla silindi.", 200);
    }

    // ================================================================
    // INTERNAL AUTHORIZATION
    // ================================================================

    private function authorizeClassroomGroup(Group $group)
    {
        $user = auth()->user();
        // Admin → sınırsız yetki
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($group->type !== \App\Enums\GroupType::Classroom) {
            abort(403, 'Bu endpoint yalnızca sınıf sohbet grupları içindir.');
        }


        $userSchoolId = $user->getSchoolId();

        if (!$userSchoolId) {
            abort(403, 'Herhangi bir okula bağlı değilsiniz.');
        }

        // Manager → sadece kendi okulundaki classroom gruplarını yönetebilir
        if ($user->hasRole('manager') && $group->school_id === $userSchoolId) {
            return true;
        }

        // Teacher → yalnızca kendi sınıf grubunda işlem yapabilir
        if ($user->hasRole('teacher')) {
            if ($group->class_model_id === $user->teacherProfile?->class_model_id) {
                return true;
            }
        }

        abort(403, 'Bu sınıf sohbet grubunu yönetme yetkiniz yok.');
    }
}
