<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\School;
use App\Models\SchoolSession;
use App\Http\Requests\Teacher\StoreAttendanceRequest;
use App\Traits\ApiResponser;
use App\Http\Requests\Teacher\UpdateSingleAttendanceRequest;

/**
 * @OA\Tag(
 * name="Attendance Management",
 * description="Ders Oturumuna Ait Yoklama Yönetimi (Teacher/Manager)"
 * )
 */
class AttendanceController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     * path="/api/schools/{school}/teacher/sessions/{session}/attendance",
     * operationId="getAttendanceBySession",
     * tags={"Attendance Management"},
     * summary="Belirtilen ders oturumuna ait mevcut yoklama kayıtlarını listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="SchoolSession ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Attendance"))),
     * @OA\Response(response=404, description="Oturum kaydı belirtilen okula ait değil"),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school, SchoolSession $session)
    {
        if ($session->school_id !== $school->id) {
            return response()->json(['message' => 'Oturum kaydı belirtilen okula ait değil.'], 404);
        }

        // Policy: Oturumu yönetmeye veya görüntülemeye yetkili olmalı
        $this->authorize('viewAttendance', $session);

        $attendanceRecords = $session->attendances()->with('studentProfile')->get();
        return $this->successResponse($attendanceRecords);
    }

    /**
     * @OA\Post(
     * path="/api/schools/{school}/teacher/sessions/{session}/attendance",
     * operationId="recordAttendance",
     * tags={"Attendance Management"},
     * summary="Belirtilen ders oturumu için toplu yoklama kaydı oluşturur/günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="SchoolSession ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreAttendanceRequest")),
     * @OA\Response(response=201, description="Başarılı (Yoklama kayıtları)"),
     * @OA\Response(response=404, description="Oturum kaydı belirtilen okula ait değil"),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function store(StoreAttendanceRequest $request, School $school, SchoolSession $session)
    {
        if ($session->school_id !== $school->id) {
            return response()->json(['message' => 'Oturum kaydı belirtilen okula ait değil.'], 404);
        }

        $this->authorize('recordAttendance', $session);

        // Toplu yoklama kaydetme/güncelleme mantığı
        $records = $request->validated('attendance_records');
        $savedRecords = [];

        foreach ($records as $record) {
            // Eşleşen kaydı bul veya yeni bir tane oluştur
            $attendance = Attendance::updateOrCreate(
                [
                    'school_session_id' => $session->id,
                    'student_id' => $record['student_id'],
                ],
                [
                    'status' => $record['status'], // 'present', 'absent', 'late', 'excused'
                    'note' => $record['note'] ?? null,
                    'recorded_by_user_id' => auth()->id(),
                ]
            );
            $savedRecords[] = $attendance;
        }
        return $this->successResponse($savedRecords, 'Yoklama başarıyla kaydedildi.', 201);
    }

    /**
     * @OA\Get(
     * path="/api/schools/{school}/teacher/sessions/{session}/attendance/{attendance}",
     * operationId="showAttendanceRecord",
     * tags={"Attendance Management"},
     * summary="Belirli bir öğrenciye ait tekil yoklama kaydını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="SchoolSession ID"),
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer"), description="Attendance Kayıt ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/Attendance")),
     * @OA\Response(response=404, description="Kayıt bulunamadı veya oturuma ait değil")
     * )
     */
    public function show(School $school, SchoolSession $session, Attendance $attendance)
    {
        // 1. Yetki Kontrolü
        $this->authorize('viewAttendance', $session);

        // 2. Aitlik Kontrolü
        if ($attendance->school_session_id !== $session->id || $session->school_id !== $school->id) {
            return response()->json(['message' => 'Yoklama kaydı belirtilen oturum/okula ait değil.'], 404);
        }
        return $this->successResponse($attendance->load('studentProfile'));
    }

    /**
     * @OA\Put(
     * path="/api/schools/{school}/teacher/sessions/{session}/attendance/{attendance}",
     * operationId="updateAttendanceRecord",
     * tags={"Attendance Management"},
     * summary="Belirli bir öğrenciye ait tekil yoklama kaydını günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="SchoolSession ID"),
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer"), description="Attendance Kayıt ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateSingleAttendanceRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/Attendance")),
     * @OA\Response(response=404, description="Kayıt bulunamadı veya oturuma ait değil")
     * )
     */
    public function update(UpdateSingleAttendanceRequest $request, School $school, SchoolSession $session, Attendance $attendance)
    {
        // 1. Yetki Kontrolü
        $this->authorize('recordAttendance', $session);

        // 2. Aitlik Kontrolü
        if ($attendance->school_session_id !== $session->id || $session->school_id !== $school->id) {
            return response()->json(['message' => 'Yoklama kaydı belirtilen oturum/okula ait değil.'], 404);
        }

        $attendance->update($request->validated());
        return $this->successResponse($attendance);
    }

    /**
     * @OA\Delete(
     * path="/api/schools/{school}/teacher/sessions/{session}/attendance/{attendance}",
     * operationId="deleteAttendanceRecord",
     * tags={"Attendance Management"},
     * summary="Belirli bir öğrenciye ait tekil yoklama kaydını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer"), description="SchoolSession ID"),
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer"), description="Attendance Kayıt ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Kayıt bulunamadı veya oturuma ait değil")
     * )
     */
    public function destroy(School $school, SchoolSession $session, Attendance $attendance)
    {
        // 1. Yetki Kontrolü
        $this->authorize('recordAttendance', $session);

        // 2. Aitlik Kontrolü
        if ($attendance->school_session_id !== $session->id || $session->school_id !== $school->id) {
            return response()->json(['message' => 'Yoklama kaydı belirtilen oturum/okula ait değil.'], 404);
        }

        $attendance->delete();
        return $this->successResponse(null, 204);
    }
}
