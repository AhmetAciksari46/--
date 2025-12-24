<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponser;

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
     * @OA\Get(
     *   path="/api/chat/notifications/unread-count",
     *   tags={"Chat Notifications"},
     *   summary="Okunmamış bildirim sayısı",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Okunmamış bildirim sayısı")
     * )
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'status' => true,
            'unread' => $count,
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
