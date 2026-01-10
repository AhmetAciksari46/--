<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;

use Illuminate\Http\Request;
use App\Models\Grade;

/**
 * @OA\Tag(
 *     name="Admin - Grades",
 *     description="Sınıf seviyelerini yönetme (yalnızca Admin)"
 * )
 */
class GradeController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/grades",
     *     tags={"Admin - Grades"},
     *     summary="Tüm sınıf seviyelerini listele (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Seviyeler listelendi"),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('grade.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        return $this->successResponse(Grade::all(), 'Seviyeler başarıyla getirildi.', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/grades/store",
     *     tags={"Admin - Grades"},
     *     summary="Yeni sınıf seviyesi oluştur (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="5. Sınıf"),
     *             @OA\Property(property="description", type="string", example="5. sınıf öğrencileri için genel seviye")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Seviye oluşturuldu"),
     *     @OA\Response(response=422, description="Doğrulama hatası"),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('grade.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255|unique:grades,name',
                'description' => 'nullable|string'
            ],
            [
                'name.required' => 'Seviye adı zorunludur.',
                'name.string' => 'Seviye adı metin türünde olmalıdır.',
                'name.max' => 'Seviye adı en fazla 255 karakter olabilir.',
                'name.unique' => 'Bu seviye adı zaten kayıtlı.',
                'description.string' => 'Açıklama metin türünde olmalıdır.'
            ]
        );

        $grade = Grade::create($validated);
        return $this->successResponse($grade, 'Seviye başarıyla oluşturuldu.', 200);
    }


    /**
     * @OA\Get(
     *   path="/api/admin/grades/{grade}",
     *   tags={"Admin - Grades"},
     *   summary="Seviye getir",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *       name="grade",
     *       in="path",
     *       required=true,
     *       description="Grade ID",
     *       @OA\Schema(type="integer")
     *   ),
     *
     *   @OA\Response(response=200, description="Seviye başarıyla getirildi."),
     *   @OA\Response(response=404, description="Seviye bulunamadı.")
     * )
     */
    public function show(Grade $grade)
    {
        if (!auth()->user()->can('grade.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        return $this->successResponse($grade, 'Seviye getirildi.', 200);
    }




    /**
     * @OA\Put(
     *     path="/api/admin/grades/{grade}",
     *     tags={"Admin - Grades"},
     *     summary="Bir sınıf seviyesini güncelle (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="grade", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Güncellenmiş Seviye Adı"),
     *             @OA\Property(property="description", type="string", example="Yeni açıklama")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Seviye güncellendi"),
     *     @OA\Response(response=404, description="Seviye bulunamadı")
     * )
     */
    public function update(Request $request, Grade $grade)
    {
        if (!auth()->user()->can('grade.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $validated = $request->validate(
            [
                'name' => 'sometimes|string|max:255|unique:grades,name,' . $grade->id,
                'description' => 'nullable|string'
            ],
            [
                'name.required' => 'Seviye adı zorunludur.',
                'name.string' => 'Seviye adı metin türünde olmalıdır.',
                'name.max' => 'Seviye adı en fazla 255 karakter olabilir.',
                'name.unique' => 'Bu seviye adı zaten kayıtlı.',
                'description.string' => 'Açıklama metin türünde olmalıdır.'
            ]
        );

        $grade->update($validated);
        return $this->successResponse($grade, 'Seviye başarıyla güncellendi.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/grades/{id}",
     *     tags={"Admin - Grades"},
     *     summary="Bir sınıf seviyesini sil (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Seviye silindi"),
     *     @OA\Response(response=404, description="Seviye bulunamadı")
     * )
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('grade.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return $this->successResponse(null, 'Seviye başarıyla silindi.', 200);
    }
}
