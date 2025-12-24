<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\CreateMessageRequest;
use App\Http\Requests\Chat\UpdateMessageRequest;
use App\Models\Group;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use App\Enums\GroupType;
use Carbon\Carbon;
use App\Traits\ApiResponser;
use App\Models\LastReadMessage;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Chat Messages",
 *     description="Gruplardaki mesajların yönetim endpointleri"
 * )
 */
class MessageController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/chat/groups/{group}/messages",
     *     tags={"Chat Messages"},
     *     summary="Bir grupda ki mesajları listeleme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of messages")
     * )
     */
    public function index(Group $group)
    {
        $this->authorizeGroupAccess($group);
        return $this->successResponse($group->messages()->with('user', 'attachments')->paginate(20), "Grup mesajları getirildi", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/chat/groups/{group}/messages",
     *     tags={"Chat Messages"},
     *     summary="Gruba mesaj gönderme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="grup ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CreateMessageRequest")),
     *     @OA\Response(response=201, description="Message created"),
     *     @OA\Response(response=422, description="Validation error example",
     *         @OA\JsonContent(example={
     *             "message": "The given data was invalid.",
     *             "errors": {
     *                 "message_type": {"Message type is required."}
     *             }
     *         })
     *     )
     * )
     */
    public function store(CreateMessageRequest $request, Group $group)
    {

        Gate::authorize('send-message', $group);

        //$this->authorizeMessageSending($group);
        $user = auth()->user();

        $message = Message::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'content' => $request->content,
            'message_type' => $request->message_type,
        ]);

        $members = $group->members()
            ->where('user_id', '!=', $user->id)
            ->get(['user_id']);

        foreach ($members as $member) {
            Notification::create([
                'user_id' => $member->user_id,
                'type' => 'new_message',
                'data' => [
                    'group_id' => $group->id,
                    'message_id' => $message->id,
                    'sender_id' => $message->user_id,
                    'preview' => $message->content
                        ? \Illuminate\Support\Str::limit($message->content, 50)
                        : null,
                ],
            ]);
        }


        LastReadMessage::updateOrCreate(
            [
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'last_read_message_id' => $message->id,
                'last_read_at' => now(),
            ]
        );
        return $this->successResponse($message, "Mesaj oluşturuldu", 200);
    }

    /**
     * @OA\Put(
     *     path="/api/chat/messages/{message}",
     *     tags={"Chat Messages"},
     *     summary="Bir Mesajı Güncelleme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateMessageRequest")),
     *     @OA\Response(response=200, description="Message updated")
     * )
     */
    public function update(UpdateMessageRequest $request, Message $message)
    {
        $this->authorizeMessageOwner($message);

        $message->update([
            'content' => $request->content ?? $message->content,
            'message_type' => $request->message_type ?? $message->message_type,
            'edited_at' => Carbon::now(),
        ]);
        return $this->successResponse($message, "Mesaj güncellendi", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/messages/{message}",
     *     tags={"Chat Messages"},
     *     summary="Mesaj silme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Message deleted")
     * )
     */
    public function destroy(Message $message)
    {
        $this->authorizeMessageOwner($message);

        $message->status = 'deleted';
        $message->save();
        $message->delete();
        return $this->successResponse(null, "Mesaj silindi.", 200);

        return response()->json(['message' => 'Message deleted']);
    }


    /**
     * @OA\Post(
     *     path="/api/chat/messages/{message}/pin",
     *     tags={"Chat Messages"},
     *     summary="Pin a message inside the group",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Message pinned")
     * )
     */
    public function pin(Message $message)
    {
        $group = $message->group;

        $this->authorizePin($group);

        $group->pinned_message_id = $message->id;
        $group->save();
        return $this->successResponse($message, "Mesaj gruba pinlendi", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/messages/{message}/pin",
     *     tags={"Chat Messages"},
     *     summary="Unpin message from group",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Pinned message removed")
     * )
     */
    public function unpin(Message $message)
    {
        $group = $message->group;

        $this->authorizePin($group);

        $group->pinned_message_id = null;
        $group->save();
        return $this->successResponse(null, "Pinlenen mesaj kaldırıldı.", 200);
    }

    // ---------------------------------------------
    // INTERNAL PERMISSION FUNCTIONS
    // ---------------------------------------------

    private function authorizeGroupAccess(Group $group)
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('manager')) {
            if ($user->managerProfile->school_id !== $group->school_id) {
                return $this->errorResponse("Bu gruba üye değilsiniz..", 403);
            }
            return true;
        }
        if (!auth()->user()->isMemberOf($group)) {
            return $this->errorResponse("Bu gruba üye değilsiniz..", 403);
        }
    }

    private function authorizeMessageSending(Group $group)
    {
        $user = auth()->user();

        if (!$user->isMemberOf($group)) {
            abort(403, 'You cannot send messages to this group.');
        }

        // Classroom → only teacher sends messages
        if ($group->type === GroupType::Classroom && !$user->hasRole('teacher')) {
            abort(403, 'Only teacher can send messages in classroom groups.');
        }

        // SchoolGeneral → requires permission
        if ($group->type === GroupType::SchoolGeneral && !$user->can('general_chat_send')) {
            abort(403, 'You do not have permission to send messages in this school group.');
        }
    }

    private function authorizeMessageOwner(Message $message)
    {
        if ($message->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'You cannot edit or delete this message.');
        }
    }

    private function authorizePin(Group $group)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Sadece adminler mesajı sabitleyebilir.');
        }
    }
}
