<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolHasGrade;
use App\Models\School;
use App\Models\Grade;

/**
 * @OA\Tag(
 *     name="SchoolHasGrade",
 *     description="Okul - seviye (grade) ilişkilerini yönetme"
 * )
 */
class SchoolHasGradeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/school-has-grades/by-school/{school_id}",
     *     tags={"SchoolHasGrades"},
     *     summary="Belirli bir okulun tüm sınıf seviyelerini getir (Sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school_id",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Response(response=200, description="Veriler başarıyla getirildi"),
     *     @OA\Response(response=404, description="Okul bulunamadı")
     * )
     */
    public function getBySchoolId($school_id)
    {
        // Okul var mı kontrol et
        $school = School::find($school_id);

        if (!$school) {
            return response()->json([
                'status' => false,
                'message' => 'Okul bulunamadı.'
            ], 404);
        }

        // İlişkili grade kayıtlarını getir
        $records = SchoolHasGrade::where('school_id', $school_id)
            ->with(['school', 'grade'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $records
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/admin/school-has-grades",
     *     tags={"SchoolHasGrades"},
     *     summary="Tüm okul-seviye ilişkilerini listele (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste döndürüldü")
     * )
     */
    public function index()
    {
        $records = SchoolHasGrade::with(['school', 'grade'])->get();

        return response()->json([
            'status' => true,
            'data' => $records
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/school-has-grades",
     *     tags={"SchoolHasGrades"},
     *     summary="Okula sınıf seviyesi ekle (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"school_id", "grade_id"},
     *             @OA\Property(property="school_id", type="integer", example=3),
     *             @OA\Property(property="grade_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Kayıt oluşturuldu")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
        ]);

        $record = SchoolHasGrade::firstOrCreate($validated);

        return response()->json([
            'status' => true,
            'message' => 'Okul-seviye ilişkisi başarıyla oluşturuldu.',
            'data' => $record->load(['school', 'grade'])
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/school-has-grades/{id}",
     *     tags={"SchoolHasGrades"},
     *     summary="Okulun bir sınıf seviyesini sil (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="İlişki ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(response=200, description="Silme başarılı")
     * )
     */
    public function destroy($id)
    {
        $record = SchoolHasGrade::findOrFail($id);
        $record->delete();

        return response()->json([
            'status' => true,
            'message' => 'Okul-seviye ilişkisi başarıyla silindi.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/manager/my-grades",
     *     tags={"SchoolHasGrades"},
     *     summary="Manager/Teacher/Student kendi okulunun sınıf seviyelerini getirir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Kayıtlar getirildi")
     * )
     */
    public function myGrades()
    {
        $user = Auth::user();

        // School ID'yi kullanıcıdan al
        $schoolId =
            $user->managerProfile->school_id ??
            $user->teacherProfile->school_id ??
            $user->studentProfile->school_id ??
            null;

        if (!$schoolId) {
            return response()->json([
                'status' => false,
                'message' => 'Okul bilgisi bulunamadı.'
            ], 403);
        }

        $records = SchoolHasGrade::where('school_id', $schoolId)
            ->with(['school', 'grade'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $records
        ]);
    }
}
