<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Admin - AcademicYear İşlemleri",
 *     description="Akademik yıl yönetimi (Admin)"
 * )
 */
class AcademicYearController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/academic-years",
     *     summary="Tüm akademik yılları listeler",
     *     tags={"Admin - AcademicYear İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Akademik yıllar listelendi",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Akademik yıllar listelendi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AcademicYear")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('academicyear.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $years = AcademicYear::orderByDesc('id')->get();

        return $this->successResponse(
            $years,
            "Akademik yıllar listelendi.",
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/admin/academic-years",
     *     summary="Yeni akademik yıl oluşturur",
     *     tags={"Admin - AcademicYear İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="2024-2025"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-09-01", nullable=true),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-06-30", nullable=true),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Akademik yıl oluşturuldu",
     *         @OA\JsonContent(ref="#/components/schemas/AcademicYear")
     *     ),
     *     @OA\Response(response=422, description="Validasyon hatası")
     * )
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('academicyear.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $request->validate([
            'name'       => 'required|string|max:50|unique:academic_years,name',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Eğer gelen is_default true ise, diğer defaultları kapat
            if ($request->boolean('is_default')) {
                AcademicYear::where('is_default', true)->update(['is_default' => false]);
            }

            $year = AcademicYear::create([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_active'  => $request->has('is_active') ? (bool)$request->is_active : true,
                'is_default' => $request->has('is_default') ? (bool)$request->is_default : false,
            ]);

            DB::commit();

            return $this->successResponse(
                $year,
                "Akademik yıl oluşturuldu.",
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse("Akademik yıl oluşturulurken hata: " . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/academic-years/{academicYear}",
     *     summary="Tek bir akademik yıl detayını getirir",
     *     tags={"Admin - AcademicYear İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="academicYear",
     *         in="path",
     *         required=true,
     *         description="AcademicYear ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Akademik yıl detayı getirildi",
     *         @OA\JsonContent(ref="#/components/schemas/AcademicYear")
     *     ),
     *     @OA\Response(response=404, description="Bulunamadı")
     * )
     */
    public function show(AcademicYear $academicYear)
    {
        if (!auth()->user()->can('academicyear.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        return $this->successResponse(
            $academicYear,
            "Akademik yıl detayı getirildi.",
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/admin/academic-years/{academicYear}",
     *     summary="Akademik yılı günceller",
     *     tags={"Admin - AcademicYear İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="academicYear",
     *         in="path",
     *         required=true,
     *         description="AcademicYear ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="2025-2026"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-09-01", nullable=true),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-06-30", nullable=true),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Güncellendi"),
     *     @OA\Response(response=422, description="Validasyon hatası")
     * )
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        if (!auth()->user()->can('academicyear.update')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }

        $request->validate([
            'name'       => 'nullable|string|max:50|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Eğer bu kayıt default yapılacaksa, diğer defaultları kapat
            if ($request->has('is_default') && $request->boolean('is_default')) {
                AcademicYear::where('is_default', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_default' => false]);
            }

            $academicYear->update($request->only([
                'name',
                'start_date',
                'end_date',
                'is_active',
                'is_default',
            ]));

            DB::commit();

            return $this->successResponse(
                $academicYear,
                "Akademik yıl güncellendi.",
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse("Akademik yıl güncellenirken hata: " . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/academic-years/{academicYear}",
     *     summary="Akademik yılı siler",
     *     tags={"Admin - AcademicYear İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="academicYear",
     *         in="path",
     *         required=true,
     *         description="AcademicYear ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=200, description="Silindi"),
     *     @OA\Response(response=403, description="Default yıl silinemez")
     * )
     */
    public function destroy(AcademicYear $academicYear)
    {
        if (!auth()->user()->can('academicyear.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }



        $academicYear->delete();

        return $this->successResponse(
            null,
            "Akademik yıl silindi.",
            200
        );
    }
}
