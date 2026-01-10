<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponser;
use App\Models\Group;
use App\Models\Message;
use App\Models\LastReadMessage;


/**
 * @OA\Tag(
 *   name="Chat Notifications",
 *   description="Chat bildirim yönetimi"
 * )
 */
class NotificationController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/chat/notifications",
     *   tags={"Chat Notifications"},
     *   summary="Kullanıcının bildirimlerini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Bildirim listesi")
     * )
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Bildirimler getirildi.',
            'data' => $notifications,
        ]);
    }


    /**
     * @OA\Post(
     *   path="/api/chat/notifications/{notification}/read",
     *   tags={"Chat Notifications"},
     *   summary="Bildirim okundu olarak işaretle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="notification",
     *     in="path",
     *     required=true,
     *     description="Bildirim ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Bildirim okundu")
     * )
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Bu bildirim size ait değil.');
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Bildirim okundu olarak işaretlendi.',
        ]);
    }


    /**
     * @OA\Post(
     *   path="/api/chat/notifications/read-all",
     *   tags={"Chat Notifications"},
     *   summary="Tüm bildirimleri okundu olarak işaretle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Tüm bildirimler okundu olarak işaretlendi",
     *     @OA\JsonContent(example={
     *       "status": true,
     *       "message": "Tüm bildirimler okundu olarak işaretlendi.",
     *       "data": {
     *         "updated": 12
     *       }
     *     })
     *   )
     * )
     */
    public function markAllAsRead()
    {
        $updated = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Tüm bildirimler okundu olarak işaretlendi.',
            'data' => [
                'updated' => $updated
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/chat/notifications/read-group/{group}",
     *   tags={"Chat Notifications"},
     *   summary="Belirli bir gruba ait tüm bildirimleri okundu yap",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="group",
     *     in="path",
     *     required=true,
     *     description="Grup ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Grup bildirimleri okundu olarak işaretlendi",
     *     @OA\JsonContent(example={
     *       "status": true,
     *       "message": "Gruba ait tüm bildirimler okundu olarak işaretlendi.",
     *       "data": {
     *         "updated": 8
     *       }
     *     })
     *   )
     * )
     */
    public function markGroupAsRead(Group $group)
    {
        $user = auth()->user();

        // ✅ Güvenlik: Kullanıcı bu gruba erişebiliyor mu?
        if (!$user->isMemberOf($group)) {
            abort(403, 'Bu gruba erişiminiz yok.');
        }

        // ✅ Grup içindeki son mesaj id
        $lastMessageId = Message::where('group_id', $group->id)->max('id');

        // Eğer grupta hiç mesaj yoksa bile "okundu" set etmeye gerek yok
        if (!$lastMessageId) {
            return response()->json([
                'status' => true,
                'message' => 'Bu grupta mesaj yok.',
                'data' => [
                    'group_id' => $group->id,
                    'last_read_message_id' => null
                ]
            ], 200);
        }

        // ✅ last_read_message_id update / create
        LastReadMessage::updateOrCreate(
            [
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'last_read_message_id' => $lastMessageId
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Grubun tüm mesajları okundu olarak işaretlendi.',
            'data' => [
                'group_id' => $group->id,
                'last_read_message_id' => $lastMessageId
            ]
        ], 200);
    }



    /**
     * @OA\Delete(
     *   path="/api/chat/notifications/{notification}",
     *   tags={"Chat Notifications"},
     *   summary="Bir bildirimi sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="notification",
     *     in="path",
     *     required=true,
     *     description="Bildirim ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Bildirim silindi",
     *     @OA\JsonContent(example={
     *       "status": true,
     *       "message": "Bildirim silindi."
     *     })
     *   )
     * )
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return $this->errorResponse("Bu bildirimi silemezsiniz, yetki hatası.", 403);
        }

        $notification->delete();

        return response()->json([
            'status' => true,
            'message' => 'Bildirim silindi.'
        ], 200);
    }
}
