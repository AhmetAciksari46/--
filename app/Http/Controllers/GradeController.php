<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;

/**
 * @OA\Tag(
 *     name="Grades",
 *     description="Sınıf seviyelerini yönetme (yalnızca Admin)"
 * )
 */
class GradeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/grades",
     *     tags={"Grades"},
     *     summary="Tüm sınıf seviyelerini listele (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Seviyeler listelendi"),
     *     @OA\Response(response=403, description="Yetkisiz erişim")
     * )
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Grade::all()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/grades/store",
     *     tags={"Grades"},
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grades,name',
            'description' => 'nullable|string'
        ]);

        $grade = Grade::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Seviye başarıyla oluşturuldu.',
            'data' => $grade
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/grades/{id}",
     *     tags={"Grades"},
     *     summary="Bir sınıf seviyesini güncelle (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
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
    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:grades,name,' . $grade->id,
            'description' => 'nullable|string'
        ]);

        $grade->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Seviye başarıyla güncellendi.',
            'data' => $grade
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/grades/{id}",
     *     tags={"Grades"},
     *     summary="Bir sınıf seviyesini sil (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Seviye silindi"),
     *     @OA\Response(response=404, description="Seviye bulunamadı")
     * )
     */
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return response()->json([
            'status' => true,
            'message' => 'Seviye başarıyla silindi.'
        ]);
    }
}
