<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ReactionRequest;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Chat Reactions",
 *     description="Manage reactions for messages and comments"
 * )
 */
class ReactionController extends Controller
{
    use ApiResponser;

    // ============================================================
    // MESSAGE REACTIONS
    // ============================================================

    /**
     * @OA\Post(
     *     path="/api/chat/messages/{message}/reactions",
     *     tags={"Chat Reactions"},
     *     summary="Add reaction to a message",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ReactionRequest")),
     *     @OA\Response(response=201, description="Reaction added"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function addMessageReaction(ReactionRequest $request, Message $message)
    {
        $group = $message->group;

        if (!auth()->user()->isMemberOf($group)) {
            return $this->errorResponse("Yetki hatası. Bu gruba üye değilsiniz.", 403);
        }

        $existing = MessageReaction::where([
            'message_id' => $message->id,
            'user_id' => auth()->id(),
            'reaction' => $request->reaction
        ])->first();

        if ($existing) {
            return $this->errorResponse("Bu tepki mevcut.", 409);
        }

        $reaction = MessageReaction::create([
            'message_id' => $message->id,
            'user_id' => auth()->id(),
            'reaction' => $request->reaction,
        ]);
        return $this->successResponse($reaction, "Mesaja tepki oluşturuldu.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/messages/{message}/reactions/{reaction}",
     *     tags={"Chat Reactions"},
     *     summary="Remove a reaction from a message",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reaction removed")
     * )
     */
    public function removeMessageReaction(Message $message, string $reaction)
    {
        $deleted = MessageReaction::where([
            'message_id' => $message->id,
            'user_id' => auth()->id(),
            'reaction' => $reaction
        ])->delete();
        if ($deleted) {
            return $this->successResponse($message, "Tepki silindi.", 200);
        }
        return $this->errorResponse("Tepki bulunamadı.", 404);
    }

    /**
     * @OA\Get(
     *     path="/api/chat/messages/{message}/reactions",
     *     tags={"Chat Reactions"},
     *     summary="List all reactions for a message",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reaction list")
     * )
     */
    public function listMessageReactions(Message $message)
    {
        return $this->successResponse($message->reactions()->with('user')->get(), "Tepkiler getirildi.", 200);
    }

    // ============================================================
    // COMMENT REACTIONS
    // ============================================================

    /**
     * @OA\Post(
     *     path="/api/chat/comments/{comment}/reactions",
     *     tags={"Chat Reactions"},
     *     summary="Add reaction to a comment",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ReactionRequest")),
     *     @OA\Response(response=201, description="Reaction added")
     * )
     */
    public function addCommentReaction(ReactionRequest $request, Comment $comment)
    {
        $group = $comment->message->group;

        if (!auth()->user()->isMemberOf($group)) {
            return $this->errorResponse("Yetki hatası. bu grupda tepki oluşturamazsınız", 403);
        }

        $existing = CommentReaction::where([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
            'reaction' => $request->reaction
        ])->first();

        if ($existing) {
            return $this->errorResponse("Tepki mevcut.", 409);
        }

        $reaction = CommentReaction::create([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
            'reaction' => $request->reaction,
        ]);
        return $this->successResponse($reaction, "Tepki oluşturuldu.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/comments/{comment}/reactions/{reaction}",
     *     tags={"Chat Reactions"},
     *     summary="Remove a reaction from a comment",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reaction removed")
     * )
     */
    public function removeCommentReaction(Comment $comment, string $reaction)
    {
        $deleted = CommentReaction::where([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
            'reaction' => $reaction
        ])->delete();
        if ($deleted) {
            return $this->successResponse($reaction, "Tepki silindi.", 200);
        }
        return $this->errorResponse("Tepki bulunamadı.", 404);
    }

    /**
     * @OA\Get(
     *     path="/api/chat/comments/{comment}/reactions",
     *     tags={"Chat Reactions"},
     *     summary="List all reactions for a comment",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reaction list")
     * )
     */
    public function listCommentReactions(Comment $comment)
    {
        return $this->successResponse($comment->reactions()->with('user')->get(), "Tepki listesi getirildi.", 200);
    }
}
