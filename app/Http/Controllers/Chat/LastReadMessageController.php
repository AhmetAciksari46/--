<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\UpdateLastReadMessageRequest;
use App\Models\Group;
use App\Models\LastReadMessage;
use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *   name="Chat Last Read",
 *   description="Chat okundu/okunmadı işlemleri"
 * )
 */
class LastReadMessageController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/chat/groups/{group}/last-read",
     *   tags={"Chat Last Read"},
     *   summary="Grup için son okunan mesajı güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="group",
     *     in="path",
     *     required=true,
     *     description="Grup ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=false,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateLastReadMessageRequest")
     *   ),
     *   @OA\Response(response=200, description="Son okunan mesaj güncellendi")
     * )
     */
    public function update(UpdateLastReadMessageRequest $request, Group $group)
    {
        $user = auth()->user();

        if (!$user->isMemberOf($group)) {
            abort(403, 'Bu gruba erişim yetkiniz yok.');
        }

        $lastMessageId = $request->last_read_message_id
            ?? Message::where('group_id', $group->id)->max('id');

        if (!$lastMessageId) {
            return response()->json([
                'status' => true,
                'message' => 'Bu grupta henüz mesaj yok.',
            ], 200);
        }

        $record = LastReadMessage::updateOrCreate(
            [
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'last_read_message_id' => $lastMessageId,
                'last_read_at' => now(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Son okunan mesaj güncellendi.',
            'data' => $record,
        ], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/chat/groups/{group}/unread-count",
     *   tags={"Chat Last Read"},
     *   summary="Grup için okunmamış mesaj sayısı",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="group",
     *     in="path",
     *     required=true,
     *     description="Grup ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Okunmamış mesaj sayısı")
     * )
     */
    public function unreadCount(Group $group)
    {
        $user = auth()->user();

        if (!$user->isMemberOf($group)) {
            abort(403, 'Bu gruba erişim yetkiniz yok.');
        }

        $lastRead = LastReadMessage::where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->first();

        $count = Message::where('group_id', $group->id)
            ->when(
                $lastRead,
                fn($q) =>
                $q->where('id', '>', $lastRead->last_read_message_id)
            )
            ->count();

        return response()->json([
            'status' => true,
            'unread' => $count,
        ]);
    }


    /**
     * @OA\Get(
     *   path="/api/chat/unread-summary",
     *   tags={"Chat Last Read"},
     *   summary="Kullanıcının üye olduğu tüm gruplar için okunmamış mesaj sayılarını getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Okunmamış mesaj özet listesi",
     *     @OA\JsonContent(
     *       example={
     *         "status": true,
     *         "message": "Okunmamış mesaj özeti getirildi.",
     *         "data": {
     *           "6": 3,
     *           "7": 0,
     *           "10": 12
     *         }
     *       }
     *     )
     *   )
     * )
     */
    public function unreadSummary()
    {
        $user = auth()->user();

        // Kullanıcının üye olduğu gruplar (senin projende bu ilişki nasıl ise ona göre düzenle)
        // Örnek: $groups = $user->groups()->pluck('groups.id');
        //$groupIds = $user->groups()->pluck('groups.id'); // <-- isMemberOf kullandığına göre muhtemelen böyle bir ilişki var
        $groupIds = $user->accessibleGroupIds();

        if ($groupIds->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Kullanıcının üye olduğu grup bulunamadı.',
                'data' => (object)[],
            ], 200);
        }

        /**
         * Amaç:
         * - Her grup için son mesaj id'sini bul
         * - Her grup için kullanıcının last_read_message_id'sini bul
         * - unread = messages_count where id > last_read_message_id
         *
         * Performans:
         * - Tek tek döngüyle query atmak yerine toplu alıyoruz.
         */

        // 1) Her grup için son mesaj id (max id)
        $lastMessageByGroup = Message::select('group_id', DB::raw('MAX(id) as last_message_id'))
            ->whereIn('group_id', $groupIds)
            ->groupBy('group_id')
            ->pluck('last_message_id', 'group_id'); // [group_id => last_message_id]

        // 2) Kullanıcının her grup için last_read_message_id’si
        $lastReadByGroup = LastReadMessage::where('user_id', $user->id)
            ->whereIn('group_id', $groupIds)
            ->pluck('last_read_message_id', 'group_id'); // [group_id => last_read_message_id]

        // 3) Unread hesapla (grup bazlı)
        // Not: last_read yoksa => tüm mesajlar unread sayılabilir. Bunun için "first message id" değil "count" gerek.
        // Basit ve doğru yaklaşım: unread sayısını tek query ile groupBy alalım.

        // Kullanıcının last_read değerlerini join’leyeceğimiz bir alt sorgu gibi düşün.
        // Pratik yol: her grup için "eşik id" belirleyip message count almak.
        // Laravel'de grup bazlı farklı eşik için en doğru yaklaşım iki adımlı hesap:
        // - last_read yoksa: count(all)
        // - last_read varsa: count(where id > last_read)

        // 3a) last_read olmayan gruplar => tüm mesajlar unread
        $groupsWithoutLastRead = $groupIds->diff($lastReadByGroup->keys());

        $unreadAllCounts = [];
        if ($groupsWithoutLastRead->isNotEmpty()) {
            $unreadAllCounts = Message::select('group_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('group_id', $groupsWithoutLastRead)
                ->groupBy('group_id')
                ->pluck('cnt', 'group_id')
                ->toArray();
        }

        // 3b) last_read olan gruplar => id > last_read
        // Bunu tam tek query yapmak SQL CASE ile mümkün ama Laravel’de okunabilir ve güvenli hali:
        // grup grup id eşiğini kullanarak where koşulu üretmek yerine,
        // last_read olan gruplar için "count after threshold" hesaplayacağız:
        $unreadAfterCounts = [];

        $groupsWithLastRead = $lastReadByGroup->keys();

        if ($groupsWithLastRead->isNotEmpty()) {
            // Bu kısım için en basit yöntem: union/case ile tek query olabilir,
            // ama okunabilirlik için minimal bir döngü yapıyoruz.
            // Grup sayın çok yüksekse burada daha ileri optimizasyon (raw SQL) yaparız.
            foreach ($groupsWithLastRead as $gid) {
                $threshold = (int) $lastReadByGroup[$gid];

                $unreadAfterCounts[$gid] = Message::where('group_id', $gid)
                    ->where('id', '>', $threshold)
                    ->count();
            }
        }

        // 4) Birleştir + boş grupları da 0 yap
        $summary = [];

        foreach ($groupIds as $gid) {
            $summary[$gid] = 0;

            // Eğer grupta hiç mesaj yoksa 0 kalsın
            if (!isset($lastMessageByGroup[$gid])) {
                continue;
            }

            if (array_key_exists($gid, $unreadAllCounts)) {
                $summary[$gid] = (int) $unreadAllCounts[$gid];
                continue;
            }

            if (array_key_exists($gid, $unreadAfterCounts)) {
                $summary[$gid] = (int) $unreadAfterCounts[$gid];
                continue;
            }

            // last_read var ama unread yok => 0
        }

        return response()->json([
            'status' => true,
            'message' => 'Okunmamış mesaj özeti getirildi.',
            'data' => $summary,
        ], 200);
    }
}
