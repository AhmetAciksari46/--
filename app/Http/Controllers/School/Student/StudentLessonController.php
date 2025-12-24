<?php

namespace App\Http\Controllers\School\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\SchoolStudentProfile;
use App\Models\LessonSession;

/**
 * @OA\Tag(
 *     name="Students Ders Programı",
 *     description="Öğrenci ders oturumları yönetimi"
 * )
 */
class StudentLessonController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{student}/today-lessons",
     *     tags={"Students Ders Programı"},
     *     summary="Öğrencinin bugünkü ders programını listeler",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student",
     *         in="path",
     *         required=true,
     *         description="Öğrenci profil ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bugünkü ders programı başarıyla listelendi"
     *     ),
     *     @OA\Response(response=403, description="Yetkisiz işlem")
     * )
     */
    public function todayLessons(School $school, SchoolStudentProfile $student)
    {
        if (!auth()->user()->can('studentlesson.view.list')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        // Öğrenci bu okula mı ait?
        if ($student->school_id !== $school->id) {
            return $this->errorResponse("Öğrenci bu okula ait değil.", 403);
        }

        // Öğrencinin sınıfı
        $classId = $student->class_model_id;

        if (!$classId) {
            return $this->errorResponse("Öğrencinin sınıf bilgisi bulunamadı.", 404);
        }
        try {
            // Bugünün tarihi
            $today = now()->toDateString();

            // Bugün o sınıfa ait öğrenci hangi derslere girecek?
            $sessions = LessonSession::where('date', $today)
                ->whereHas('schedule', function ($q) use ($classId, $school) {
                    $q->where('class_model_id', $classId)
                        ->where('school_id', $school->id);
                })
                ->with([
                    'schedule',
                    'schedule.subject',
                    'schedule.classModel',
                    'teacher',
                    'physicalClassroom'
                ])
                ->orderBy('schedule.start_time')
                ->get();

            return $this->successResponse($sessions, "Öğrencinin bugünkü ders programı listelendi.");
        } catch (\Exception $e) {
            return $this->errorResponse("Ders oturumları getirilirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/students/{student}/next-week-lessons",
     *     tags={"Students Ders Programı"},
     *     summary="Öğrencinin bugünden başlayarak gelecek 1 haftalık ders programını listeler",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student",
     *         in="path",
     *         required=true,
     *         description="Öğrenci profil ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Gelecek 1 haftalık ders programı listelendi"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Yetki hatası veya yanlış okul"
     *     )
     * )
     */

    public function nextWeekLessons(School $school, SchoolStudentProfile $student)
    {
        if (!auth()->user()->can('studentlesson.view.list')) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        // 1) Güvenlik: öğrenci bu okula mı ait?
        if ($student->school_id !== $school->id) {
            return $this->errorResponse("Öğrenci bu okula ait değildir.", 403);
        }

        // 2) Öğrencinin sınıfı
        $classId = $student->class_model_id;

        if (!$classId) {
            return $this->errorResponse("Öğrencinin sınıf bilgisi bulunamadı.", 404);
        }
        try {
            // 3) Tarih aralığı (bugün → +7 gün)
            $startDate = now()->startOfDay();
            $endDate = now()->addDays(7)->endOfDay();

            // 4) Bu sınıfın gelecek 1 haftalık dersleri
            $sessions = LessonSession::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereHas('schedule', function ($query) use ($classId, $school) {
                    $query->where('class_model_id', $classId)
                        ->where('school_id', $school->id);
                })
                ->with([
                    'schedule',
                    'schedule.subject',
                    'schedule.classModel',
                    'teacher',
                    'physicalClassroom'
                ])
                ->orderBy('date')
                ->orderBy('schedule.start_time')
                ->get();

            return $this->successResponse(
                $sessions,
                "Öğrencinin gelecek 1 haftalık ders programı listelendi."
            );
        } catch (\Exception $e) {
            return $this->errorResponse("Ders oturumları getirilirken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }
}
