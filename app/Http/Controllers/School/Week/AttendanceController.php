<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Traits\ApiResponser;
use App\Models\LessonSession;
use App\Models\School;
use App\Http\Requests\Attendance\AttendanceStoreRequest;
use App\Http\Requests\Attendance\AttendanceUpdateRequest;
use App\Http\Requests\Attendance\BatchAttendanceRequest;

/**
 * @OA\Tag(
 *     name="Manager & Teacher Attendance",
 *     description="Yoklama sistemi yönetimi"
 * )
 */
class AttendanceController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Ders oturumuna ait tüm yoklamaları listele",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Liste başarıyla getirildi")
     * )
     */
    public function index(School $school, LessonSession $session)
    {
        if (!auth()->user()->can('attendance.view')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        if ($session->schedule->school_id != $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        $attendance = Attendance::where('lesson_session_id', $session->id)
            ->with(['student'])
            ->get();

        return $this->successResponse($attendance, "Yoklamalar listelendi.", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Bu ders oturumu için yoklama oluştur",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AttendanceStoreRequest")),
     *
     *     @OA\Response(response=201, description="Yoklama oluşturuldu")
     * )
     */
    public function store(AttendanceStoreRequest $request, School $school, LessonSession $session)
    {
        if (!auth()->user()->can('attendance.create')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($session->schedule->school_id != $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        $data = $request->validated();
        $data['lesson_session_id'] = $session->id;

        // Eğer entered_at yoksa otomatik şu anki zamana ayarla
        if (!isset($data['entered_at'])) {
            $data['entered_at'] = now();
        }

        $attendance = Attendance::create($data);

        return $this->successResponse($attendance, "Yoklama oluşturuldu.", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances/{attendance}",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Tekil yoklama detayını getir",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Detay başarıyla getirildi")
     * )
     */
    public function show(School $school, LessonSession $session, Attendance $attendance)
    {
        if (!auth()->user()->can('attendance.view')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if (
            $attendance->lesson_session_id != $session->id ||
            $session->schedule->school_id != $school->id
        ) {
            return $this->errorResponse("Bu yoklama bu oturuma veya okula ait değildir.", 403);
        }

        return $this->successResponse($attendance->load(['student']), "Başarıyla getirildi", 200);
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances/{attendance}",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Yoklama güncelle",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AttendanceUpdateRequest")),
     *
     *     @OA\Response(response=200, description="Güncellendi")
     * )
     */
    public function update(AttendanceUpdateRequest $request, School $school, LessonSession $session, Attendance $attendance)
    {
        if (!auth()->user()->can('attendance.update')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if (
            $attendance->lesson_session_id != $session->id ||
            $session->schedule->school_id != $school->id
        ) {
            return $this->errorResponse("Bu yoklama bu oturuma veya okula ait değildir.", 403);
        }

        $attendance->update($request->validated());

        return $this->successResponse($attendance, "Yoklama güncellendi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances/{attendance}",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Yoklamayı sil",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Yoklama silindi")
     * )
     */
    public function destroy(School $school, LessonSession $session, Attendance $attendance)
    {
        if (!auth()->user()->can('attendance.delete')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if (
            $attendance->lesson_session_id != $session->id ||
            $session->schedule->school_id != $school->id
        ) {
            return $this->errorResponse("Bu yoklama bu oturuma veya okula ait değildir.", 403);
        }

        $attendance->delete();

        return $this->successResponse([], "Yoklama silindi.", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/lesson-sessions/{session}/attendances/batch",
     *     tags={"Manager & Teacher Attendance"},
     *     summary="Bir derse ait öğrencilerin yoklamasını toplu şekilde kaydeder.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BatchAttendanceRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Toplu yoklama başarıyla kaydedildi"),
     *     @OA\Response(response=403, description="Yetki yok"),
     *     @OA\Response(response=422, description="Validasyon hatası")
     * )
     */

    public function batchStore(BatchAttendanceRequest $request, School $school, LessonSession $session)
    {
        if (!auth()->user()->can('attendance.update')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        // Oturum okula ait mi?
        if ($session->schedule->school_id !== $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        $data = $request->validated()['attendances'];

        $created = [];
        $updated = [];

        foreach ($data as $item) {

            // Daha önce yoklama var mı?
            $existing = Attendance::where('lesson_session_id', $session->id)
                ->where('student_id', $item['student_id'])
                ->first();

            if ($existing) {
                // Var olan yoklamayı güncelle
                $existing->update([
                    'status' => $item['status'],
                    'absent_excuse_note' => $item['absent_excuse_note'] ?? null,
                    'entered_at' => now(),
                ]);

                $updated[] = $existing;
            } else {
                // Yeni yoklama oluştur
                $created[] = Attendance::create([
                    'lesson_session_id' => $session->id,
                    'student_id' => $item['student_id'],
                    'status' => $item['status'],
                    'absent_excuse_note' => $item['absent_excuse_note'] ?? null,
                    'entered_at' => now(),
                ]);
            }
        }

        return $this->successResponse([
            'created' => $created,
            'updated' => $updated
        ], "Toplu yoklama kaydedildi.");
    }
}
