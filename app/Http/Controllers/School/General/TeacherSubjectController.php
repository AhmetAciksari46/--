<?php


namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\Subject;
use App\Traits\ApiResponser;
use App\Models\School;

/**
 * @OA\Tag(
 *     name="TeacherSubjects",
 *     description="Öğretmen-Ders ilişkilerini yönet (Admin veya Manager)"
 * )
 */
class TeacherSubjectController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teacher-subjects",
     *     tags={"TeacherSubjects"},
     *     summary="Tüm öğretmen-ders ilişkilerini getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Liste döndürüldü")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('teachersubject.view.list')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $subjects = TeacherSubject::whereHas('teacher.teacherProfile', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
                ->with(['teacher.teacherProfile', 'subject'])
                ->get();

            return $this->successResponse(
                $subjects,
                'Bu okula ait öğretmen-ders ilişkileri başarıyla getirildi.',
                200
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/teacher-subjects",
     *     tags={"TeacherSubjects"},
     *     summary="Öğretmene ders ata",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"teacher_id","subject_id"},
     *             @OA\Property(property="teacher_id", type="integer", example=7),
     *             @OA\Property(property="subject_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Atama başarılı")
     * )
     */
    public function store(Request $request, School $school)
    {
        if (!auth()->user()->can('teachersubject.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        try {
            $validated = $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'subject_id' => 'required|exists:subjects,id',
            ]);

            $teacher = User::with('teacherProfile')->findOrFail($validated['teacher_id']);

            $subject = Subject::with('branch')->findOrFail($validated['subject_id']);

            if ($teacher->teacherProfile->branch_id !== $subject->branch_id) {
                return $this->errorResponse('Branş uyuşmuyor.', 422);
            }


            $record = TeacherSubject::firstOrCreate($validated);
            return $this->successResponse($record->load(['teacher', 'subject']), 'Öğretmen-Ders ataması başarıyla oluşturuldu.', 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/teacher-subjects/{id}",
     *     tags={"TeacherSubjects"},
     *     summary="Öğretmen-Ders atamasını sil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Silindi")
     * )
     */
    public function destroy($id, School $school)
    {
        if (!auth()->user()->can('teachersubject.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        $this->authorize('delete', $record);

        $record = TeacherSubject::findOrFail($id);
        $record->delete();
        return $this->successResponse(null, 'Öğretmen-Ders ataması başarıyla silindi.', 200);
    }
}
