<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\School;
use App\Models\SchoolSession;
use App\Http\Requests\Teacher\StoreAttendanceRequest;
use App\Traits\ApiResponser;
use App\Http\Requests\Teacher\UpdateSingleAttendanceRequest;
use App\Models\LessonSession;

/**
 * @OA\Tag(
 *     name="Attendance",
 *     description="Yoklama sistemi yönetimi"
 * )
 */
class AttendanceController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/attendances",
     *     tags={"Attendance"},
     *     summary="Tüm yoklama kayıtlarını listele (Admin/Manager/Teacher)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Yoklama kayıtları listesi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', Attendance::class);

        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $attendances = Attendance::whereHas('session', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
                ->with(['student', 'session.schedule.class'])
                ->get();
        } elseif ($user->hasRole('manager')) {
            $attendances = Attendance::whereHas('session.schedule.class', function ($q) use ($user) {
                $q->where('school_id', $user->managerProfile->school_id);
            })
                ->with(['student', 'session.schedule.class'])
                ->get();
        } else {
            $attendances = Attendance::with(['student', 'session.schedule.class'])->get();
        }

        return response()->json([
            'status' => true,
            'data' => $attendances
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/attendances/store",
     *     tags={"Attendance"},
     *     summary="Bir ders oturumu için yoklama oluştur (Sadece Teacher veya Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lesson_session_id","attendances"},
     *             @OA\Property(property="lesson_session_id", type="integer", example=3),
     *             @OA\Property(
     *                 property="attendances",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=12),
     *                     @OA\Property(property="status", type="string", example="present")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Yoklama başarıyla kaydedildi"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_session_id' => 'required|exists:lesson_sessions,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
        ]);

        $lesson = LessonSession::with('schedule.class')->findOrFail($validated['lesson_session_id']);
        $this->authorize('create', [Attendance::class, $lesson]);

        $records = [];
        foreach ($validated['attendances'] as $att) {
            $records[] = Attendance::updateOrCreate(
                [
                    'class_schedule_id' => $lesson->class_schedule_id,
                    'student_id' => $att['student_id'],
                    'date' => $lesson->date
                ],
                [
                    'status' => $att['status']
                ]
            );
        }

        // Oturumu tamamlandı olarak işaretle
        $lesson->update(['is_completed' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Yoklama başarıyla kaydedildi.',
            'data' => $records
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/attendances/session/{lesson_session_id}",
     *     tags={"Attendance"},
     *     summary="Belirli bir oturuma ait yoklama listesini getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lesson_session_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Yoklama listesi"),
     *     @OA\Response(response=404, description="Oturum bulunamadı")
     * )
     */
    public function getBySession($lesson_session_id)
    {
        $lesson = LessonSession::findOrFail($lesson_session_id);
        $this->authorize('view', [Attendance::class, $lesson]);

        $attendances = Attendance::where('class_schedule_id', $lesson->class_schedule_id)
            ->where('date', $lesson->date)
            ->with('student')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $attendances
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/attendances/{id}",
     *     tags={"Attendance"},
     *     summary="Yoklama kaydını güncelle (Teacher veya Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="absent")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Yoklama güncellendi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $this->authorize('update', $attendance);

        $validated = $request->validate([
            'status' => 'required|in:present,absent,late,excused'
        ]);

        $attendance->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Yoklama kaydı güncellendi.',
            'data' => $attendance
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/attendances/{id}",
     *     tags={"Attendance"},
     *     summary="Yoklama kaydını sil (Sadece Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Kayıt silindi")
     * )
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json([
            'status' => true,
            'message' => 'Yoklama kaydı silindi.'
        ]);
    }
}
