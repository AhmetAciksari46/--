<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentDetail\StoreContentDetailRequest;
use App\Http\Requests\ContentDetail\UpdateContentDetailRequest;
use App\Models\Content;
use App\Models\ContentDetail;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *   name="Admin Content Details",
 *   description="ContentDetail (payload) operations"
 * )
 *
 * @OA\Schema(
 *     schema="ContentDetail",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=101),
 *     @OA\Property(property="content_id", type="integer", example=15),
 *     @OA\Property(property="payload", type="object"),
 *     @OA\Property(property="created_at", type="string", example="2025-12-30T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", example="2025-12-30T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="ContentDetailStoreRequest",
 *     type="object",
 *     required={"payload"},
 *     @OA\Property(property="payload", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ContentDetailUpdateRequest",
 *     type="object",
 *     required={"payload"},
 *     @OA\Property(property="payload", type="object")
 * )
 */
class ContentDetailController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/contents/{contentId}/detail",
     *   tags={"Admin Content Details"},
     *   summary="Content'in detail kaydını getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="contentId", in="path", required=true, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="Content detail getirildi", @OA\JsonContent(ref="#/components/schemas/ContentDetail")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function showByContent($contentId)
    {
        if (!auth()->user()->can('content.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $content = Content::with('detail')->find($contentId);
        if (!$content || !$content->detail) {
            return $this->errorResponse('Content detail bulunamadı.', 404);
        }

        return $this->successResponse($content->detail, 'Content detail getirildi.', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/contents/{contentId}/detail",
     *   tags={"Admin Content Details"},
     *   summary="Content'e detail oluştur (yoksa)",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="contentId", in="path", required=true, @OA\Schema(type="integer", example=15)),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ContentDetailStoreRequest")),
     *   @OA\Response(response=200, description="Detail oluşturuldu", @OA\JsonContent(ref="#/components/schemas/ContentDetail")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Content bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function storeByContent(StoreContentDetailRequest $request, $contentId)
    {
        if (!auth()->user()->can('content.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $content = Content::find($contentId);
        if (!$content) {
            return $this->errorResponse('Content bulunamadı.', 404);
        }

        // 1 content = 1 aktif detail yaklaşımı
        if ($content->detail) {
            return $this->errorResponse('Bu content için zaten detail mevcut.', 422);
        }

        try {
            $detail = ContentDetail::create([
                'content_id' => $content->id,
                'payload' => $request->payload,
            ]);

            return $this->successResponse($detail, 'Content detail oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Detail oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/admin/content-details/{id}",
     *   tags={"Admin Content Details"},
     *   summary="Content detail güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=101)),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ContentDetailUpdateRequest")),
     *   @OA\Response(response=200, description="Detail güncellendi", @OA\JsonContent(ref="#/components/schemas/ContentDetail")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateContentDetailRequest $request, ContentDetail $contentDetail)
    {
        if (!auth()->user()->can('content.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $contentDetail->update([
                'payload' => $request->payload,
            ]);

            return $this->successResponse($contentDetail, 'Content detail güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Detail güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/content-details/{id}",
     *   tags={"Admin Content Details"},
     *   summary="Content detail sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=101)),
     *   @OA\Response(response=200, description="Detail silindi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function destroy(ContentDetail $contentDetail)
    {
        if (!auth()->user()->can('content.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $contentDetail->delete();
        return $this->successResponse(null, 'Content detail silindi.', 200);
    }
}
