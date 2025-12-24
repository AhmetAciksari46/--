<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\MessageAttachment;
use App\Models\CommentAttachment;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Chat\StoreMessageAttachmentRequest;
use App\Models\Message;
use App\Models\Comment;
use App\Traits\ApiResponser;

use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Chat Attachments",
 *     description="File management for message and comment attachments"
 * )
 */
class AttachmentController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Post(
     *   path="/api/chat/messages/{message}/attachments",
     *   tags={"Chat Attachments"},
     *   summary="Mesaja dosya ekle",
     *   security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *     required=true,
     *     content={
     *       @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/StoreMessageAttachmentRequest")
     *       )
     *     }
     *   ),
     *   @OA\Response(response=201, description="Dosya yüklendi")
     * )
     */
    public function store(StoreMessageAttachmentRequest $request, Message $message)
    {

        $user = Auth::user();

        // Yetki: mesajı yazan veya admin
        if ($message->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Bu mesaja dosya ekleme yetkiniz yok.');
        }

        $file = $request->file('file');

        $path = $file->store('chat/messages', 'public');

        $attachment = MessageAttachment::create([
            'message_id' => $message->id,
            'file_path'  => $path,
            'file_name'  => $file->getClientOriginalName(),
            'mime_type'  => $file->getClientMimeType(),
            'size'       => $file->getSize(),
            'type'       => $file->extension(),
        ]);
        return $this->successResponse(
            $attachment,
            'Mesaj dosyası başarıyla yüklendi.',
            200
        );
    }


    /**
     * @OA\Get(
     *     path="/api/chat/messages/{message}/attachments",
     *     tags={"Chat Attachments"},
     *     summary="Get message attachment info",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Attachment details")
     * )
     */
    public function showMessageAttachment(MessageAttachment $attachment)
    {
        return $this->successResponse(
            $attachment,
            'Mesaj dosyası getirildi.',
            200
        );
    }

    /**
     * @OA\Get(
     *     path="/api/chat/attachments/message/{attachment}/download",
     *     tags={"Chat Attachments"},
     *     summary="Download message attachment file",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="File downloaded")
     * )
     */
    public function downloadMessageAttachment(MessageAttachment $attachment)
    {

        $this->authorizeAttachmentAccess($attachment->message->group_id);
        return Storage::download($attachment->file_path, $attachment->file_name);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/attachments/message/{attachment}",
     *     tags={"Chat Attachments"},
     *     summary="Delete message attachment file",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Attachment deleted")
     * )
     */
    public function deleteMessageAttachment(MessageAttachment $attachment)
    {
        $this->authorizeAttachmentDeletion($attachment);

        Storage::delete($attachment->file_path);
        $attachment->delete();
        return $this->successResponse(
            $attachment,
            'Mesaj dosyası başarıyla silindi.',
            200
        );
    }


    // ======================================================
    // COMMENT ATTACHMENTS
    // ======================================================

    /**
     * @OA\Get(
     *     path="/api/chat/attachments/comment/{attachment}",
     *     tags={"Chat Attachments"},
     *     summary="Get comment attachment info",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Attachment details")
     * )
     */
    public function showCommentAttachment(CommentAttachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment->comment->message->group_id);
        return $this->successResponse(
            $attachment,
            'Yorum dosyaları başarıyla getirildi.',
            200
        );
    }

    /**
     * @OA\Get(
     *     path="/api/chat/attachments/comment/{attachment}/download",
     *     tags={"Chat Attachments"},
     *     summary="Download comment attachment",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="File downloaded")
     * )
     */
    public function downloadCommentAttachment(CommentAttachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment->comment->message->group_id);

        return Storage::download($attachment->file_path, $attachment->file_name);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/attachments/comment/{attachment}",
     *     tags={"Chat Attachments"},
     *     summary="Delete comment attachment",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         description="attachment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Attachment deleted")
     * )
     */
    public function deleteCommentAttachment(CommentAttachment $attachment)
    {
        $this->authorizeAttachmentDeletion($attachment);

        Storage::delete($attachment->file_path);
        $attachment->delete();
        return $this->successResponse(
            $attachment,
            'Yorum dosyası başarıyla silindi.',
            200
        );
    }
    /**
     * @OA\Post(
     *   path="/api/chat/comments/{comment}/attachments",
     *   tags={"Chat Attachments"},
     *   summary="Mesaja dosya ekle",
     *   security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *     required=true,
     *     content={
     *       @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/StoreMessageAttachmentRequest")
     *       )
     *     }
     *   ),
     *   @OA\Response(response=201, description="Dosya yüklendi")
     * )
     */
    public function storee(StoreMessageAttachmentRequest $request, Comment $comment)
    {

        $user = Auth::user();

        // Yetki: mesajı yazan veya admin
        if ($comment->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Bu mesaja dosya ekleme yetkiniz yok.');
        }

        $file = $request->file('file');

        $path = $file->store('chat/messages', 'public');

        $attachment = CommentAttachment::create([
            'comment_id' => $comment->id,
            'file_path'  => $path,
            'file_name'  => $file->getClientOriginalName(),
            'mime_type'  => $file->getClientMimeType(),
            'size'       => $file->getSize(),
            'type'       => $file->extension(),
        ]);
        return $this->successResponse(
            $attachment,
            'Yorum dosyası başarıyla oluşturuldu.',
            200
        );
    }
    // ======================================================
    // INTERNAL AUTHORIZATION LOGIC
    // ======================================================

    private function authorizeAttachmentAccess($groupId)
    {
        $user = Auth::user();

        $group = \App\Models\Group::findOrFail($groupId);

        if (!auth()->user()->isMemberOf($group) && !$user->hasRole('admin')) {
            return $this->errorResponse(
                "Dahil olmadığınız bir grup dosyasını görüntüleme yetkiniz yoktur.",
                403
            );
        }
    }

    private function authorizeAttachmentDeletion($attachment)
    {
        $user = auth()->user();

        // Admin → her şeyi silebilir
        if ($user->hasRole('admin')) return true;

        // Attachment message/comment owner'ına ait olmalı
        if ($attachment instanceof MessageAttachment) {
            if ($attachment->message->user_id === $user->id) return true;
        }

        if ($attachment instanceof CommentAttachment) {
            if ($attachment->comment->user_id === $user->id) return true;
        }
        return $this->errorResponse(
            "Bu dosyayı silme yetkiniz yoktur.",
            403
        );
    }
}
