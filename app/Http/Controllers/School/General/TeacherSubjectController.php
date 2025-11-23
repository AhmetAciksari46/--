<?php


namespace App\Http\Controllers\School\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\Subject;

/**
 * @OA\Tag(
 *     name="TeacherSubjects",
 *     description="Öğretmen-Ders ilişkilerini yönet (Admin veya Manager)"
 * )
 */
class TeacherSubjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/teacher-subjects",
     *     tags={"TeacherSubjects"},
     *     summary="Tüm öğretmen-ders ilişkilerini getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste döndürüldü")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', TeacherSubject::class);
        return response()->json(
            TeacherSubject::with(['teacher', 'subject'])->get()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/teacher-subjects",
     *     tags={"TeacherSubjects"},
     *     summary="Öğretmene ders ata",
     *     security={{"bearerAuth":{}}},
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = User::with('teacherProfile')->findOrFail($validated['teacher_id']);
        $subject = Subject::with('branch')->findOrFail($validated['subject_id']);

        if ($teacher->teacherProfile->branch_id !== $subject->branch_id) {
            return response()->json(['error' => 'Branş uyuşmuyor.'], 422);
        }

        $this->authorize('create', [TeacherSubject::class, $teacher->id]);

        $record = TeacherSubject::firstOrCreate($validated);
        return response()->json($record->load(['teacher', 'subject']), 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/teacher-subjects/{id}",
     *     tags={"TeacherSubjects"},
     *     summary="Öğretmen-Ders atamasını sil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Silindi")
     * )
     */
    public function destroy($id)
    {
        $record = TeacherSubject::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete();

        return response()->json(['message' => 'Silindi'], 204);
    }
}
