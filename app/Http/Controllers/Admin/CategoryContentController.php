<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *   name="Admin Category Contents",
 *   description="Category - Content ilişkisi (category_content pivot) işlemleri"
 * )
 */
class CategoryContentController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/categories/{id}/contents",
     *   tags={"Admin Category Contents"},
     *   summary="Kategoriye bağlı content listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Kategoriye bağlı content listesi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Kategori bulunamadı")
     * )
     */
    public function index(Category $category)
    {
        if (!auth()->user()->can('category.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $contents = $category->contents()->get();
        return $this->successResponse($contents, 'Kategori içerikleri başarıyla getirildi.', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/categories/{id}/contents",
     *   tags={"Admin Category Contents"},
     *   summary="Kategoriye content bağla",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"content_id"},
     *       @OA\Property(property="content_id", type="integer", example=15)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Content kategoriye bağlandı"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Kategori / Content bulunamadı")
     * )
     */
    public function attach(Category $category)
    {
        if (!auth()->user()->can('category.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $contentId = request()->input('content_id');

        if (!$contentId) {
            return $this->errorResponse('content_id alanı zorunludur.', 422);
        }

        $content = Content::find($contentId);
        if (!$content) {
            return $this->errorResponse('Content bulunamadı.', 404);
        }

        // Aynı content daha önce bağlandıysa tekrar bağlamaz
        $category->contents()->syncWithoutDetaching([$contentId]);

        return $this->successResponse(null, 'Content kategoriye başarıyla bağlandı.', 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/categories/{id}/contents/{contentId}",
     *   tags={"Admin Category Contents"},
     *   summary="Kategoriden content kaldır",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="contentId", in="path", required=true, @OA\Schema(type="integer", example=15)),
     *   @OA\Response(response=200, description="Content kategoriden kaldırıldı"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Kategori / Content bulunamadı")
     * )
     */
    public function detach(Category $category, $contentId)
    {
        if (!auth()->user()->can('category.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $content = Content::find($contentId);
        if (!$content) {
            return $this->errorResponse('Content bulunamadı.', 404);
        }

        $category->contents()->detach($contentId);

        return $this->successResponse(null, 'Content kategoriden başarıyla kaldırıldı.', 200);
    }
}
