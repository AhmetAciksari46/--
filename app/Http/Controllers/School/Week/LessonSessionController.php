<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\LessonSession;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Http\Requests\LessonSession\LessonSessionStoreRequest;
use App\Http\Requests\LessonSession\LessonSessionUpdateRequest;
use App\Http\Requests\LessonSession\LessonSessionGenerateRequest;

/**
 * @OA\Tag(
 *     name="Manager & Teacher LessonSession",
 *     description="Gerçekleşen ders oturumları yönetimi"
 * )
 */

class LessonSessionController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/lesson-sessions",
     *     tags={"Manager & Teacher LessonSession"},
     *     summary="Okuldaki tüm ders oturumlarını listele",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Liste oluşturuldu"),
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('lessonsession.view',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        $sessions = LessonSession::whereHas('schedule', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })
            ->with(['schedule.classModel', 'schedule.subject', 'teacher', 'physicalClassroom'])
            ->get();

        return $this->successResponse($sessions, "Ders oturumları listelendi.");
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/lesson-sessions",
     *     tags={"LessonSession"},
     *     summary="Yeni ders oturumu oluştur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LessonSessionStoreRequest")),
     *     @OA\Response(response=201, description="Başarıyla oluşturuldu")
     * )
     */
    public function store(LessonSessionStoreRequest $request, School $school)
    {
        if (!auth()->user()->can('lessonsession.create',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        $data = $request->validated();

        $schedule = ClassSchedule::find($data['class_schedule_id']);

        if (!$schedule || $schedule->school_id !== $school->id) {
            return $this->errorResponse("Bu ders planı bu okula ait değildir.", 403);
        }


        $session = LessonSession::create($data);

        return $this->successResponse($session, "Ders oturumu başarıyla oluşturuldu.", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/lesson-sessions/{session}",
     *     tags={"Manager & Teacher LessonSession"},
     *     summary="Ders oturumu detayını getir",
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detay getirildi")
     * )
     */
    public function show(School $school, LessonSession $session)
    {
        if (!auth()->user()->can('lessonsession.view',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($session->schedule->school_id != $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        return $this->successResponse(
            $session->load(['schedule', 'schedule.subject', 'teacher', 'physicalClassroom'], "Detay getirildi", 200)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/lesson-sessions/generate",
     *     tags={"LessonSession"},
     *     summary="Belirtilen ders planları için belirtilen hafta sayısı kadar otomatik ders oturumu oluşturur.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LessonSessionGenerateRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Ders oturumları başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="Yetki hatası"),
     *     @OA\Response(response=422, description="Validasyon hatası")
     * )
     */



    public function generate(LessonSessionGenerateRequest $request, School $school)
    {
        if (!auth()->user()->can('lessonsession.create')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        $data = $request->validated();

        $scheduleIds = $data['schedule_ids'];
        $totalWeeks = $data['weeks'] ?? 40; // <-- HAFTA PARAMETRESİ
        $startDate = \Carbon\Carbon::parse($data['start_date']);

        $createdSessions = [];

        foreach ($scheduleIds as $scheduleId) {

            $schedule = ClassSchedule::find($scheduleId);

            // Güvenlik: bu schedule belirtilen okula mı ait?
            if (!$schedule || $schedule->school_id !== $school->id) {
                return $this->errorResponse("Schedule ID {$scheduleId} bu okula ait değildir.", 403);
            }

            // Haftanın günü (monday, tuesday vs)
            $day = strtolower($schedule->day_of_week);

            // İlk haftanın ilgili günü
            $currentDate = $startDate->copy()->next($day);

            for ($week = 1; $week <= $totalWeeks; $week++) {

                $session = LessonSession::create([
                    'class_schedule_id' => $schedule->id,
                    'week_number' => $week,
                    'date' => $currentDate->toDateString(),
                    'teacher_id' => null,
                    'physical_classroom_id' => null,
                    'status' => 'scheduled',
                    'is_attendance_required' => true,
                ]);

                $createdSessions[] = $session;

                $currentDate->addWeek();
            }
        }

        return $this->successResponse($createdSessions, "Toplam {$totalWeeks} haftalık ders oturumları oluşturuldu.", 201);
    }



    /**
     * @OA\Put(
     *     path="/api/schools/{school}/lesson-sessions/{session}",
     *     tags={"Manager & Teacher LessonSession"},
     *     summary="Ders oturumunu güncelle",
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LessonSessionUpdateRequest")),
     *     @OA\Response(response=200, description="Güncellendi")
     * )
     */
    public function update(LessonSessionUpdateRequest $request, School $school, LessonSession $session)
    {
        if (!auth()->user()->can('lessonsession.update',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($session->schedule->school_id != $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        $session->update($request->validated());

        return $this->successResponse($session, "Ders oturumu güncellendi.", 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/lesson-sessions/{session}",
     *     tags={"Manager & Teacher LessonSession"},
     *     summary="Ders oturumunu sil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Silindi")
     * )
     */
    public function destroy(School $school, LessonSession $session)
    {
        if (!auth()->user()->can('lessonsession.delete',)) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }
        if ($session->schedule->school_id != $school->id) {
            return $this->errorResponse("Bu oturum bu okula ait değildir.", 403);
        }

        $session->delete();

        return $this->successResponse([], "Ders oturumu silindi.", 200);
    }
}
