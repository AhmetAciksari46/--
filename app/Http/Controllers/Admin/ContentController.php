<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreContentRequest;
use App\Http\Requests\Content\UpdateContentRequest;
use App\Models\Content;
use App\Models\ContentDetail;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *   name="Admin Contents",
 *   description="Content (Soru Havuzu) CRUD operations"
 * )
 *
 * @OA\Schema(
 *     schema="Content",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=15),
 *     @OA\Property(property="type", type="string", example="choice"),
 *     @OA\Property(property="status", type="string", example="published"),
 *     @OA\Property(property="cloned_from_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", example="2025-12-30T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", example="2025-12-30T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="ContentStoreRequest",
 *     type="object",
 *     required={"type","payload"},
 *     @OA\Property(property="type", type="string", example="choice"),
 *     @OA\Property(property="status", type="string", example="published"),
 *     @OA\Property(
 *         property="payload",
 *         type="object",
 *         example={
 *           "question": {"title":"Soru 1","text":"Metin","media_pool_id":null},
 *           "answers": {"type":"choice","options": {{"id":1,"text":"A"},{"id":2,"text":"B"},{"id":3,"text":"C"},{"id":4,"text":"D"}}},
 *           "correct": {"type":"single","value":2},
 *           "solution": {"text":"Doğru cevap B çünkü...","media_pool_id":null}
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ContentUpdateRequest",
 *     type="object",
 *     @OA\Property(property="type", type="string", example="match"),
 *     @OA\Property(property="status", type="string", example="draft"),
 *     @OA\Property(property="payload", type="object")
 * )
 */
class ContentController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/contents",
     *   tags={"Admin Contents"},
     *   summary="Content listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Content listesi"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('content.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $items = Content::with('detail')->latest()->get();
        return $this->successResponse($items, 'Content listesi başarıyla getirildi', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/contents",
     *   tags={"Admin Contents"},
     *   summary="Yeni content oluştur",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ContentStoreRequest")),
     *   @OA\Response(response=200, description="Oluşturuldu", @OA\JsonContent(ref="#/components/schemas/Content")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(StoreContentRequest $request)
    {
        if (!auth()->user()->can('content.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $content = Content::create([
                'type' => $request->type,
                'status' => $request->status ?? 'published',
                'cloned_from_id' => null,
            ]);

            ContentDetail::create([
                'content_id' => $content->id,
                'payload' => $request->payload,
            ]);

            return $this->successResponse($content->load('detail'), 'Content oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Content oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/admin/contents/{id}",
     *   tags={"Admin Contents"},
     *   summary="Content getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Content getirildi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(Content $content)
    {
        if (!auth()->user()->can('content.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        return $this->successResponse($content->load('detail'), 'Content getirildi.', 200);
    }

    /**
     * @OA\Put(
     *   path="/api/admin/contents/{id}",
     *   tags={"Admin Contents"},
     *   summary="Content güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ContentUpdateRequest")),
     *   @OA\Response(response=200, description="Güncellendi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateContentRequest $request, Content $content)
    {
        if (!auth()->user()->can('content.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $content->update($request->only(['type', 'status']));

            if ($request->has('payload')) {
                $detail = $content->detail;
                $detail->update(['payload' => $request->payload]);
            }

            return $this->successResponse($content->load('detail'), 'Content güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Content güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/contents/{id}",
     *   tags={"Admin Contents"},
     *   summary="Content sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="Silindi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function destroy(Content $content)
    {
        if (!auth()->user()->can('content.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $content->delete();
        return $this->successResponse(null, 'Content silindi.', 200);
    }
}
