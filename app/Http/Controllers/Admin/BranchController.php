<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *   name="Admin Branches",
 *   description="Branch (branş) CRUD operations"
 * )
 */
class BranchController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *   path="/api/admin/branches",
     *   tags={"Admin Branches"},
     *   summary="Branş listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Branch kayıtları listesi"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        $branches = Branch::all();

        return $this->successResponse($branches, 'Branşlar başarıyla getirildi', 200);
    }






    /**
     * @OA\Get(
     *   path="/api/manager/getactivebranches",
     *   tags={"Manager Genel İşlemleri"},
     *   summary="Aktif olan branş listesini getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *      response=200,
     *      description="Aktif branş listesi",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="İşlem başarılı."),
     *          @OA\Property(
     *              property="data",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Matematik"),
     *                  @OA\Property(property="slug", type="string", example="matematik"),
     *                  @OA\Property(property="code", type="string", example="MATH"),
     *                  @OA\Property(property="color", type="string", example="#1E90FF"),
     *              )
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Yetkiniz yok"
     *   )
     * )
     */
    public function activeBranches()
    {
        if (!Auth::user()->can('teacher.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $branches = Branch::select('id', 'name', 'code', 'color')
            ->where('is_active', 1)
            ->get();
        return $this->successResponse($branches, 'Aktif branşlar bilgisi başarıyla getirildi', 200);
    }

    /**
     * @OA\Post(
     *   path="/api/admin/branches",
     *   tags={"Admin Branches"},
     *   security={{"bearerAuth":{}}},
     *   summary="Yeni branş oluştur",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BranchStoreRequest")),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Branch"))
     * )
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return $this->successResponse($branch, 'Branş başarıyla oluşturuldu.', 200);
    }


    /**
     * @OA\Get(
     *   path="/api/admin/branches/{id}",
     *   tags={"Admin Branches"},
     *   summary="Branş getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *       response=200,
     *       description="Branş başarıyla getirildi",
     *       @OA\JsonContent(ref="#/components/schemas/Branch")
     *   ),
     *   @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(Branch $branch)
    {

        return $this->successResponse($branch, 'Branş getirildi.', 200);
    }

    /**
     * @OA\Put(
     *   path="/api/admin/branches/{id}",
     *   tags={"Admin Branches"},
     *   summary="Branş güncelle",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BranchUpdateRequest")),
     *   @OA\Response(
     *       response=200,
     *       description="Branş başarıyla güncellendi",
     *       @OA\JsonContent(ref="#/components/schemas/Branch")
     *   ),
     *   @OA\Response(response=404, description="Bulunamadı"),
     *   @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return $this->successResponse($branch, 'Branş güncellendi.', 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/branches/{branch}",
     *   tags={"Admin Branches"},
     *   summary="Branşı sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *      name="branch",
     *      in="path",
     *      required=true,
     *      description="Silinecek branş ID'si",
     *      @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *      response=204,
     *      description="Başarıyla silindi (No Content)"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Branş bulunamadı"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Yetkisiz erişim"
     *   )
     * )
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return $this->successResponse(null, 'Branş silindi.', 200);
    }
}
