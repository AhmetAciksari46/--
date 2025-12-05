<?php

namespace App\Http\Controllers\School\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\LessonSession;
use App\Traits\ApiResponser;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Teacher Ders Programı",
 *     description="Gerçekleşen ders oturumları yönetimi"
 * )
 */
class TeacherLessonController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/today-lessons",
     *     tags={"Teacher Ders Programı"},
     *     summary="Öğretmenin bugünkü derslerini listeler",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bugünkü dersler listelendi"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası veya okul eşleşmiyor"
     *     )
     * )
     */
    public function todayLessons(School $school)
    {
        $teacher = auth()->user();

        // Kullanıcı öğretmen değilse engellemek istersen buraya ek gelebilir

        $today = now()->toDateString();

        // Bu öğretmenin bugün gireceği dersler
        $sessions = LessonSession::where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->whereHas('schedule', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->with([
                'schedule',
                'schedule.subject',
                'schedule.classModel',
                'physicalClassroom'
            ])
            ->orderBy('schedule.start_time')
            ->get();

        return $this->successResponse($sessions, "Bugünkü dersler listelendi.");
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/missing-attendance",
     *     tags={"Teacher Ders Programı"},
     *     summary="Öğretmenin bugünkü yoklaması alınmamış derslerini listeler",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Yoklaması alınmamış dersler başarıyla listelendi"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz işlem"
     *     )
     * )
     */

    public function missingAttendance(School $school)
    {
        $teacher = auth()->user();
        $today = now()->toDateString();

        // Bugünkü derslerinden yoklama ALINMAMIŞ olanlar
        $sessions = LessonSession::where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->whereHas('schedule', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->whereDoesntHave('attendances') // attendance yoksa
            ->with([
                'schedule',
                'schedule.subject',
                'schedule.classModel',
                'physicalClassroom'
            ])
            ->orderBy('schedule.start_time')
            ->get();

        return $this->successResponse($sessions, "Yoklaması alınmamış dersler listelendi.");
    }
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/teachers/weekly-lessons",
     *     tags={"Teacher Ders Programı"},
     *     summary="Öğretmenin içinde bulunulan haftadaki ders programını listeler.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Haftalık ders programı listelendi"
     *     ),
     *     @OA\Response(response=403, description="Yetki hatası")
     * )
     */

    public function weeklyLessons(School $school)
    {
        $teacher = auth()->user();

        // Haftanın başlangıcı ve bitişi
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeek = now()->endOfWeek(Carbon::SUNDAY)->toDateString();

        // Bu öğretmenin bu haftaki ders oturumlarını getir
        $sessions = LessonSession::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereHas('schedule', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->with([
                'schedule',
                'schedule.subject',
                'schedule.classModel',
                'physicalClassroom'
            ])
            ->orderBy('date')
            ->orderBy('schedule.start_time')
            ->get();

        return $this->successResponse([
            'week_start' => $startOfWeek,
            'week_end' => $endOfWeek,
            'lessons' => $sessions
        ], "Bu haftaki ders programı listelendi.");
    }
}
