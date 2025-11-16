<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="ClassSchedule",
 *     description="Haftalık ders programı yönetimi"
 * )
 */
class ClassScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/classschedules",
     *     tags={"ClassSchedule"},
     *     summary="Tüm ders programlarını listele (Admin/Manager/Teacher)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Ders programları listesi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);

        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $schedules = ClassSchedule::where('teacher_id', $user->id)
                ->with(['class', 'subject'])
                ->get();
        } elseif ($user->hasRole('manager')) {
            $schedules = ClassSchedule::whereHas('class', function ($q) use ($user) {
                $q->where('school_id', $user->managerProfile->school_id);
            })->with(['class', 'subject', 'teacher'])->get();
        } else {
            $schedules = ClassSchedule::with(['class', 'subject', 'teacher'])->get();
        }

        return response()->json([
            'status' => true,
            'data' => $schedules
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/classschedules/store",
     *     tags={"ClassSchedule"},
     *     summary="Yeni ders programı oluştur (Admin/Manager)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"class_model_id","subject_id","teacher_id","day_of_week","start_time","end_time"},
     *             @OA\Property(property="class_model_id", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=2),
     *             @OA\Property(property="teacher_id", type="integer", example=5),
     *             @OA\Property(property="day_of_week", type="string", example="monday"),
     *             @OA\Property(property="start_time", type="string", example="09:00"),
     *             @OA\Property(property="end_time", type="string", example="10:00"),
     *             @OA\Property(property="physical_classroom_id", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Program oluşturuldu"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
     *     @OA\Response(response=422, description="Doğrulama hatası")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize('create', ClassSchedule::class);

        $validated = $request->validate([
            'class_model_id' => 'required|exists:class_models,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'physical_classroom_id' => 'nullable|exists:physical_classrooms,id',
        ]);

        $schedule = ClassSchedule::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Ders programı başarıyla oluşturuldu.',
            'data' => $schedule->load(['class', 'teacher', 'subject'])
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/classschedules/{id}",
     *     tags={"ClassSchedule"},
     *     summary="Belirli bir ders programını getir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Ders programı bilgisi"),
     *     @OA\Response(response=404, description="Program bulunamadı")
     * )
     */
    public function show($id)
    {
        $schedule = ClassSchedule::with(['class', 'teacher', 'subject'])->findOrFail($id);
        $this->authorize('view', $schedule);

        return response()->json([
            'status' => true,
            'data' => $schedule
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/classschedules/{id}",
     *     tags={"ClassSchedule"},
     *     summary="Ders programını güncelle (Admin/Manager)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="day_of_week", type="string", example="friday"),
     *             @OA\Property(property="start_time", type="string", example="10:00"),
     *             @OA\Property(property="end_time", type="string", example="11:00"),
     *             @OA\Property(property="physical_classroom_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Program güncellendi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function update(Request $request, $id)
    {
        $schedule = ClassSchedule::findOrFail($id);
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'day_of_week' => 'sometimes|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'physical_classroom_id' => 'nullable|exists:physical_classrooms,id',
        ]);

        $schedule->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Ders programı başarıyla güncellendi.',
            'data' => $schedule
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/classschedules/{id}",
     *     tags={"ClassSchedule"},
     *     summary="Ders programını sil (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Program silindi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function destroy($id)
    {
        $schedule = ClassSchedule::findOrFail($id);
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Ders programı başarıyla silindi.'
        ]);
    }
}
