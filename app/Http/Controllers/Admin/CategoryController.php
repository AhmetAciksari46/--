<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *   name="Admin Categories",
 *   description="Category CRUD operations"
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="2. Sınıf"),
 *     @OA\Property(property="description", type="string", nullable=true, example="2. sınıf kategori grubu"),
 *     @OA\Property(property="color", type="string", nullable=true, example="#F59E0B"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", example="2025-12-30T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", example="2025-12-30T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="CategoryStoreRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Words Ünitesi"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Kelime soruları kategorisi"),
 *     @OA\Property(property="color", type="string", nullable=true, example="#10B981"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, example=1)
 * )
 *
 * @OA\Schema(
 *     schema="CategoryUpdateRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="Words Ünitesi - Güncel"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Güncel açıklama"),
 *     @OA\Property(property="color", type="string", nullable=true, example="#3B82F6"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, example=1)
 * )
 */
class CategoryController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/categories",
     *   tags={"Admin Categories"},
     *   summary="Kategori listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Kategori listesi"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('category.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $categories = Category::query()->orderBy('id', 'desc')->get();
        return $this->successResponse($categories, 'Kategoriler başarıyla getirildi', 200);
    }

    /**
     * @OA\Get(
     *   path="/api/admin/categories/tree",
     *   tags={"Admin Categories"},
     *   summary="Kategori ağacını getir (parent-child)",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Kategori ağacı"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function tree()
    {
        if (!auth()->user()->can('category.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $tree = Category::with('children.children.children')
            ->whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse($tree, 'Kategori ağacı başarıyla getirildi', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/categories",
     *   tags={"Admin Categories"},
     *   security={{"bearerAuth":{}}},
     *   summary="Yeni kategori oluştur",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryStoreRequest")),
     *   @OA\Response(response=200, description="Kategori oluşturuldu", @OA\JsonContent(ref="#/components/schemas/Category")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        if (!auth()->user()->can('category.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $category = Category::create($request->validated());
            return $this->successResponse($category, 'Kategori oluşturuldu.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Kategori oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/admin/categories/{id}",
     *   tags={"Admin Categories"},
     *   summary="Kategori getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Kategori getirildi", @OA\JsonContent(ref="#/components/schemas/Category")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(Category $category)
    {
        if (!auth()->user()->can('category.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        return $this->successResponse($category, 'Kategori getirildi.', 200);
    }

    /**
     * @OA\Put(
     *   path="/api/admin/categories/{id}",
     *   tags={"Admin Categories"},
     *   summary="Kategori güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryUpdateRequest")),
     *   @OA\Response(response=200, description="Kategori güncellendi", @OA\JsonContent(ref="#/components/schemas/Category")),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if (!auth()->user()->can('category.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        try {
            $category->update($request->validated());
            return $this->successResponse($category, 'Kategori güncellendi.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Kategori güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/categories/{id}",
     *   tags={"Admin Categories"},
     *   summary="Kategori sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
     *   @OA\Response(response=200, description="Kategori silindi"),
     *   @OA\Response(response=403, description="Yetkiniz yok"),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function destroy(Category $category)
    {
        if (!auth()->user()->can('category.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }

        $category->delete();
        return $this->successResponse(null, 'Kategori silindi.', 200);
    }
}
