<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\CreateCommentRequest;
use App\Http\Requests\Chat\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\CommentAttachment;
use App\Models\Message;
use Illuminate\Support\Carbon;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Chat Comments",
 *     description="Mesajlara yazılan yorumların kontrolü"
 * )
 */
class CommentController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/chat/messages/{message}/comments",
     *     tags={"Chat Comments"},
     *     summary="mesaja ait tüm yorumları gör",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment list")
     * )
     */
    public function index(Message $message)
    {
        $group = $message->group;

        $user = auth()->user();

        if (!$user->isMemberOf($group)) {
            return $this->errorResponse(
                "Dahil olmadığınız bir grup mesajını görüntüleme yetkiniz yoktur.",
                403
            );
        }
        return $this->successResponse(
            $message->comments()->with('user', 'attachments')->paginate(20),
            "Yorumlar başarıyla listelendi.",
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/chat/messages/{message}/comments",
     *     tags={"Chat Comments"},
     *     summary="Bir Mesaja yorum oluşturma",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CreateCommentRequest")),
     *     @OA\Response(response=201, description="Comment created successfully")
     * )
     */
    public function store(CreateCommentRequest $request, Message $message)
    {

        Gate::authorize('create-comment', $message);

        $group = $message->group;
        $user = auth()->user();

        // COMMENT PERMISSIONS
        $this->authorizeCommentCreation($group, $message);

        $comment = Comment::create([
            'message_id' => $message->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        // FILE ATTACHMENTS
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment_attachments');

                CommentAttachment::create([
                    'comment_id' => $comment->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }
        return $this->successResponse(
            $comment->load('attachments'),
            "Yorum başarıyla oluşturuldu.",
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/chat/comments/{comment}",
     *     tags={"Chat Comments"},
     *     summary="yorum düzeltme/update",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment updated")
     * )
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorizeCommentOwner($comment);

        $comment->update([
            'content' => $request->content,
            'edited_at' => Carbon::now(),
        ]);
        return $this->successResponse(
            $comment,
            "Yorum başarıyla güncellendi.",
            200
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/comments/{comment}",
     *     tags={"Chat Comments"},
     *     summary="Yorumu silme",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment deleted")
     * )
     */
    public function destroy(Comment $comment)
    {
        $this->authorizeCommentOwner($comment);

        $comment->status = 'deleted';
        $comment->save();
        $comment->delete();
        return $this->successResponse(
            $comment,
            "Yorum başarıyla silindi.",
            200
        );
    }

    // ============================================================
    // INTERNAL AUTH CHECKS
    // ============================================================

    private function authorizeCommentCreation($group, Message $message)
    {
        $user = auth()->user();

        // Group membership check
        if (!$user->isMemberOf($group)) {
            abort(403, 'You are not a member of this group.');
        }

        // Classroom comment rules
        if ($group->type === 'classroom') {

            // Anyone except admin must be a teacher or student in that group
            if ($user->hasRole('admin')) return true;

            // Teacher – allowed
            if ($user->hasRole('teacher')) return true;

            // Student – allowed to comment but cannot create messages
            if ($user->hasRole('student')) return true;

            abort(403, 'You cannot comment in this classroom group.');
        }
    }

    private function authorizeCommentOwner(Comment $comment)
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'You cannot edit or delete this comment.');
        }
    }
}
