<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolHasGrade;
use App\Models\School;
use App\Models\Grade;
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 *     name="Admin - SchoolHasGrade",
 *     description="Okul - seviye (grade) ilişkilerini yönetme"
 * )
 */
class SchoolHasGradeController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/school-has-grades/by-school/{school}",
     *     tags={"Admin - SchoolHasGrades"},
     *     summary="Belirli bir okulun tüm sınıf seviyelerini getir (Sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="school ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Response(response=200, description="Veriler başarıyla getirildi"),
     *     @OA\Response(response=404, description="Okul bulunamadı")
     * )
     */
    public function getBySchoolId(School $school)
    {

        if (!auth()->user()->can('grade.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        // İlişkili grade kayıtlarını getir
        $records = SchoolHasGrade::where('school_id', $school->id)
            ->with(['school', 'grade'])
            ->get();
        return $this->successResponse($records, 'Okul-seviye ilişkisi başarıyla getirildi.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/school-has-grades",
     *     tags={"Admin - SchoolHasGrades"},
     *     summary="Tüm okul-seviye ilişkilerini listele (sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste döndürüldü")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('grade.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $records = SchoolHasGrade::with(['school', 'grade'])->get();
        return $this->successResponse($records, 'Okul-seviye ilişkisi başarıyla getirildi.', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/school-has-grades",
     *     tags={"Admin - SchoolHasGrades"},
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
        if (!auth()->user()->can('grade.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
        ]);

        $record = SchoolHasGrade::firstOrCreate($validated);
        return $this->successResponse($record->load(['school', 'grade'],), 'Okul-seviye ilişkisi başarıyla oluşturuldu.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/school-has-grades/{id}",
     *     tags={"Admin - SchoolHasGrades"},
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
        if (!auth()->user()->can('grade.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $record = SchoolHasGrade::findOrFail($id);
        $record->delete();
        return $this->successResponse(null, 'Okul-seviye ilişkisi başarıyla silindi.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/manager/my-grades",
     *     tags={"Admin - SchoolHasGrades"},
     *     summary="Manager/Teacher/Student kendi okulunun sınıf seviyelerini getirir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Kayıtlar getirildi")
     * )
     */
    public function myGrades()
    {
        if (!auth()->user()->can('grade.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $user = Auth::user();

        // School ID'yi kullanıcıdan al
        $schoolId =
            $user->managerProfile->school_id ??
            $user->teacherProfile->school_id ??
            $user->studentProfile->school_id ??
            null;

        if (!$schoolId) {
            return $this->errorResponse('Kullanıcıya ait okul bilgisi bulunamadı.', 403);
        }

        $records = SchoolHasGrade::where('school_id', $schoolId)
            ->with(['school', 'grade'])
            ->get();
        return $this->successResponse($records, 'Kullanıcının okuluna ait sınıf seviyeleri başarıyla getirildi.', 200);
    }
}
