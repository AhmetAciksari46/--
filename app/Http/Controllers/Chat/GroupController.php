<?php

namespace App\Http\Controllers\Chat;

use App\Enums\GroupType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\CreateClassroomGroupRequest;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\School;
use App\Models\GroupMember;
use App\Models\LastReadMessage;
use App\Models\Message;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use App\Http\Requests\Chat\UpdateGroupRequest;


/**
 * @OA\Tag(
 *     name="Chat Groups",
 *     description="Operations about chat groups (global, school, classroom)"
 * )
 */
class GroupController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/chat/groups/global",
     *     tags={"Chat Groups"},
     *     summary="Genel chat grubu",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Genel chat gruplarını listeler.",
     *     )
     * )
     */
    public function globalGroups()
    {
        $groups = Group::whereIn('type', [
            GroupType::GlobalGeneral,
            GroupType::GlobalManager,
            GroupType::GlobalYonetim,
        ])->get();
        return $this->successResponse($groups, "Genel Gruplar Getirildi", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/chat/groups",
     *     tags={"Chat Groups"},
     *     summary="Okul bazlı genel gruplar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="School ID"
     *     ),
     *     @OA\Response(response=200, description="School chat groups")
     * )
     */
    public function schoolGroups(School $school)
    {
        $groups = $school->chatGroups()->get();
        return $this->successResponse($groups, "Okulun Genel Grubu Getirildi", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/classes/{classModel}/chat/group",
     *     tags={"Chat Groups"},
     *     summary="Sınıf bazlı chat grubu",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="School ID"
     *     ),
     *     @OA\Parameter(
     *         name="classModel",
     *         in="path",
     *         required=true,
     *         description="Class ID"
     *     ),
     *     @OA\Response(response=200, description="Sınıf chat grubu")
     * )
     */
    public function classroomGroup(School $school, ClassModel $classModel)
    {
        $group = $classModel->chatGroup;

        if (!$group) {
            return $this->errorResponse("sınıf chat grubu henüz oluşturulmamış", 404);
        }
        return $this->successResponse($group, "Sınıf Chat Grubu Getirildi", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/classes/{classModel}/chat/group",
     *     tags={"Chat Groups"},
     *     summary="Sınıf Sohbet Grubu Oluştur",
     *     description="Belirtilen sınıf için yeni bir sohbet grubu oluşturur. Her sınıf yalnızca bir sohbet grubuna sahip olabilir.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID"
     *     ),
     *     @OA\Parameter(
     *         name="classModel",
     *         in="path",
     *         required=true,
     *         description="Sınıf ID"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(ref="#/components/schemas/CreateClassroomGroupRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Sınıf sohbet grubu başarıyla oluşturuldu."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bu sınıf için zaten bir sohbet grubu mevcut."
     *     )
     * )
     */


    public function createClassroomGroup(CreateClassroomGroupRequest $request, School $school, ClassModel $classModel)
    {

        $user = Auth::user();

        if ($classModel->chatGroup) {
            return $this->errorResponse("Sınıf sohbet grubu hali hazırda mevcut", 404);
        }

        // Create group record
        $group = Group::create([
            'type' => GroupType::Classroom,
            'school_id' => $classModel->school_id,
            'class_model_id' => $classModel->id,
            'name' => $request->name ?? ($classModel->name . ' Sınıf Grubu'),
            'description' => $request->description,
            'created_by' => $user->id,
        ]);

        // Add teacher
        $group->members()->create([
            'user_id' => $classModel->teacher_id,
            'role_in_group' => 'teacher',
        ]);

        // Add students
        foreach ($classModel->students as $student) {
            $group->members()->create([
                'user_id' => $student->id,
                'role_in_group' => 'student',
            ]);
        }
        return $this->successResponse($group, "Sınıf sohbet grubu oluşturuldu", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/chat/groups/{group}",
     *     tags={"Chat Groups"},
     *     summary="sohbet grubu getir.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="Group ID"
     *     ),
     *     @OA\Response(response=200, description="Group Detayları")
     * )
     */
    public function show(Group $group)
    {
        return $this->successResponse($group, "Sınıf sohbet grubu detayları getirildi.", 200);
    }

    /**
     * @OA\Get(
     *   path="/api/chat/my-groups",
     *   tags={"Chat Groups"},
     *   summary="Kullanıcının üye olduğu grupların ID listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Kullanıcının dahil olduğu grup ID'leri",
     *     @OA\JsonContent(
     *       example={
     *         "status": true,
     *         "message": "Kullanıcının dahil olduğu gruplar getirildi.",
     *         "data": {1, 3, 5}
     *       }
     *     )
     *   )
     * )
     */
    public function myGroups()
    {
        $user = auth()->user();
        $groupIds = $user->accessibleGroupIds();

        if ($groupIds->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Kullanıcının üye olduğu grup bulunamadı.',
                'data' => [],
            ], 200);
        }

        /**
         * Amaç:
         * - group id + name
         * - last_read_message_id (user bazlı)
         * - unread_count = mesaj sayısı (id > last_read_message_id)
         *
         * Not:
         * - last_read yoksa unread = tüm mesajlar
         */

        // user'ın last_read verileri: group_id -> last_read_message_id
        $lastReadByGroup = LastReadMessage::where('user_id', $user->id)
            ->whereIn('group_id', $groupIds)
            ->pluck('last_read_message_id', 'group_id');

        // Grupları getir
        $groups = Group::whereIn('id', $groupIds)
            ->select('id', 'name')
            ->get()
            ->map(function ($group) use ($lastReadByGroup) {

                $lastReadId = $lastReadByGroup[$group->id] ?? 0;

                $unreadCount = Message::where('group_id', $group->id)
                    ->when($lastReadId > 0, fn($q) => $q->where('id', '>', $lastReadId))
                    ->count();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'unread_count' => $unreadCount,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Gruplar + okunmamış mesaj sayısı getirildi.',
            'data' => $groups
        ], 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/chat/groups/{group}",
     *   tags={"Chat Groups"},
     *   summary="Sohbet grubunu sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="group",
     *     in="path",
     *     required=true,
     *     description="Group ID"
     *   ),
     *   @OA\Response(response=200, description="Grup silindi")
     * )
     */
    public function destroy(Group $group)
    {
        $user = Auth::user();

        $this->authorizeGroupManage($user, $group);

        $group->delete();

        return $this->successResponse(
            null,
            'Sohbet grubu başarıyla silindi.',
            200
        );
    }


    /**
     * @OA\Put(
     *   path="/api/chat/groups/{group}",
     *   tags={"Chat Groups"},
     *   summary="Sohbet grubunu güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="group",
     *     in="path",
     *     required=true,
     *     description="Group ID"
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateGroupRequest")
     *   ),
     *   @OA\Response(response=200, description="Grup güncellendi")
     * )
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $user = Auth::user();

        $this->authorizeGroupManage($user, $group);

        $group->update([
            'name' => $request->name ?? $group->name,
            'description' => $request->description,
        ]);

        return $this->successResponse(
            $group,
            'Sohbet grubu güncellendi.',
            200
        );
    }


    private function authorizeGroupManage($user, Group $group): void
    {
        // Admin → full access
        if ($user->hasRole('admin')) {
            return;
        }

        // Classroom group özel kuralları
        if ($group->type === GroupType::Classroom) {

            // Manager → kendi okulundaki sınıf grupları
            if (
                $user->hasRole('manager') &&
                $group->school_id === $user->getSchoolId()
            ) {
                return;
            }

            // Teacher → kendi sınıf grubu
            if (
                $user->hasRole('teacher') &&
                $group->class_model_id === $user->teacherProfile?->class_model_id
            ) {
                return;
            }
        }

        abort(403, 'Bu sohbet grubunu yönetme yetkiniz yok.');
    }
}
