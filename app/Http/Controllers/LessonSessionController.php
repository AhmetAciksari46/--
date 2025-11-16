<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LessonSession;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="LessonSession",
 *     description="Gerçekleşen ders oturumları yönetimi"
 * )
 */

class LessonSessionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/lessonsessions",
     *     tags={"LessonSession"},
     *     summary="Tüm ders oturumlarını listele (Admin/Manager/Teacher)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Oturum listesi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', LessonSession::class);

        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            // Öğretmen sadece kendi oturumlarını görür
            $sessions = LessonSession::where('teacher_id', $user->id)
                ->with(['schedule', 'teacher'])
                ->get();
        } elseif ($user->hasRole('manager')) {
            // Manager sadece kendi okuluna ait sınıfları görür
            $sessions = LessonSession::whereHas('schedule.class', function ($q) use ($user) {
                $q->where('school_id', $user->managerProfile->school_id);
            })->with(['schedule', 'teacher'])->get();
        } else {
            // Admin tümünü görebilir
            $sessions = LessonSession::with(['schedule', 'teacher'])->get();
        }

        return response()->json([
            'status' => true,
            'data' => $sessions
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/lessonsessions/store",
     *     tags={"LessonSession"},
     *     summary="Yeni ders oturumu oluştur (Teacher/Manager/Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"class_schedule_id", "date"},
     *             @OA\Property(property="class_schedule_id", type="integer", example=3),
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-10"),
     *             @OA\Property(property="is_completed", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Oturum başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize('create', LessonSession::class);

        $validated = $request->validate([
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'date' => 'required|date',
            'is_completed' => 'sometimes|boolean'
        ]);

        $schedule = ClassSchedule::findOrFail($validated['class_schedule_id']);

        $session = LessonSession::create([
            'class_schedule_id' => $schedule->id,
            'teacher_id' => $schedule->teacher_id,
            'date' => $validated['date'],
            'is_completed' => $validated['is_completed'] ?? false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Ders oturumu başarıyla oluşturuldu.',
            'data' => $session->load(['schedule', 'teacher'])
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/lessonsessions/{id}",
     *     tags={"LessonSession"},
     *     summary="Belirli bir ders oturumunu getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Oturum bilgisi"),
     *     @OA\Response(response=404, description="Oturum bulunamadı")
     * )
     */
    public function show($id)
    {
        $lesson = LessonSession::with(['schedule', 'teacher', 'attendances'])
            ->findOrFail($id);

        $this->authorize('view', $lesson);

        return response()->json([
            'status' => true,
            'data' => $lesson
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/lessonsessions/{id}",
     *     tags={"LessonSession"},
     *     summary="Ders oturumunu güncelle (Admin/Manager/Teacher kendi oturumunu)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-15"),
     *             @OA\Property(property="is_completed", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Oturum güncellendi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function update(Request $request, $id)
    {
        $lesson = LessonSession::findOrFail($id);
        $this->authorize('update', $lesson);

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'is_completed' => 'sometimes|boolean'
        ]);

        $lesson->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Oturum başarıyla güncellendi.',
            'data' => $lesson
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/lessonsessions/{id}",
     *     tags={"LessonSession"},
     *     summary="Oturumu sil (sadece admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Oturum silindi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function destroy($id)
    {
        $lesson = LessonSession::findOrFail($id);
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return response()->json([
            'status' => true,
            'message' => 'Oturum başarıyla silindi.'
        ]);
    }
}
