<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MediaPool\StoreMediaPoolRequest;
use App\Http\Requests\MediaPool\UpdateMediaPoolRequest;
use App\Models\MediaPool;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *   name="Admin Media Pools",
 *   description="MediaPool CRUD operations"
 * )
 *
 * @OA\Schema(
 *     schema="MediaPool",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/uploads/abc.jpg"),
 *     @OA\Property(property="type", type="string", example="image", enum={"image","video","audio","link","sound"}),
 *     @OA\Property(property="name", type="string", nullable=true, example="Elma görseli"),
 *     @OA\Property(property="created_at", type="string", example="2025-12-30T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", example="2025-12-30T12:00:00Z")
 * )
 *
 */
class MediaPoolController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/media-pools",
     *   tags={"Admin Media Pools"},
     *   summary="MediaPool listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="MediaPool kayıtları listesi"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('mediapool.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $items = MediaPool::query()->latest()->get();
        return $this->successResponse($items, 'MediaPool kayıtları başarıyla getirildi', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/media-pools",
     *   tags={"Admin Media Pools"},
     *   security={{"bearerAuth":{}}},
     *   summary="Yeni media kaydı oluştur",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/MediaPoolStoreRequest")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(StoreMediaPoolRequest $request)
    {
        if (!auth()->user()->can('mediapool.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $item = MediaPool::create($request->validated());
            return $this->successResponse($item, 'Media başarıyla oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Media oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/admin/media-pools/{id}",
     *   tags={"Admin Media Pools"},
     *   summary="Media kaydını getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(MediaPool $mediaPool)
    {
        if (!auth()->user()->can('mediapool.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        return $this->successResponse($mediaPool, 'Media başarıyla getirildi.', 200);
    }

    /**
     * @OA\Put(
     *   path="/api/admin/media-pools/{id}",
     *   tags={"Admin Media Pools"},
     *   summary="Media kaydını güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/MediaPoolUpdateRequest")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateMediaPoolRequest $request, MediaPool $mediaPool)
    {
        if (!auth()->user()->can('mediapool.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $mediaPool->update($request->validated());
            return $this->successResponse($mediaPool, 'Media başarıyla güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Media güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/media-pools/{id}",
     *   tags={"Admin Media Pools"},
     *   summary="Media kaydını sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
     *   @OA\Response(response=200, description="Başarıyla silindi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function destroy(MediaPool $mediaPool)
    {
        if (!auth()->user()->can('mediapool.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $mediaPool->delete();
        return $this->successResponse(null, 'Media başarıyla silindi.', 200);
    }
}
