<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ClassSchedule\ClassScheduleStoreRequest;
use App\Http\Requests\ClassSchedule\ClassScheduleUpdateRequest;

/**
 * @OA\Tag(
 *     name="Manager & Teacher - ClassSchedule",
 *     description="Haftalık ders programı yönetimi"
 * )
 */
class ClassScheduleController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/class-schedules",
     *     tags={"Manager & Teacher - ClassSchedule"},
     *     summary="Tüm sınıf ders planlarını listele",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı listeleme"
     *     ),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function index(School $school)
    {
        if (!auth()->user()->can('classschedule.view',)) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $schedules = ClassSchedule::where('school_id', $school->id)
            ->with(['classModel', 'subject'])
            ->get();

        return $this->successResponse($schedules, "Ders planları listelendi.", 200);
    }


    /**
     * @OA\Post(
     *     path="/api/schools/{school}/class-schedules",
     *     tags={"Manager & Teacher - ClassSchedule"},
     *     summary="Yeni ders planı oluştur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClassScheduleStoreRequest")),
     *     @OA\Response(response=201, description="Ders planı oluşturuldu"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function store(ClassScheduleStoreRequest $request, School $school)
    {
        if (!auth()->user()->can('classschedule.create',)) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $data = $request->validated();
        $data['school_id'] = $school->id;

        $schedule = ClassSchedule::create($data);

        return $this->successResponse($schedule, "Ders planı oluşturuldu.", 200);
    }


    /**
     * @OA\Get(
     *     path="/api/schools/{school}/class-schedules/{schedule}",
     *     tags={"Manager & Teacher - ClassSchedule"},
     *     summary="Ders planı detayı",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detay getirildi")
     * )
     */
    public function show(School $school, ClassSchedule $schedule)
    {
        if (!auth()->user()->can('classschedule.view',)) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        if ($schedule->school_id !== $school->id) {
            return $this->errorResponse("Bu okula ait değil.", 403);
        }

        return $this->successResponse($schedule->load(['classModel', 'subject']), "başarılı", 200);
    }


    /**
     * @OA\Put(
     *     path="/api/schools/{school}/class-schedules/{schedule}",
     *     tags={"Manager & Teacher - ClassSchedule"},
     *     summary="Ders planını güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClassScheduleUpdateRequest")),
     *     @OA\Response(response=200, description="Güncellendi")
     * )
     */
    public function update(ClassScheduleUpdateRequest $request, School $school, ClassSchedule $schedule)
    {
        if (!auth()->user()->can('classschedule.update',)) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        if ($schedule->school_id !== $school->id) {
            return $this->errorResponse("Bu okula ait değil.", 403);
        }

        $schedule->update($request->validated());

        return $this->successResponse($schedule, "Ders planı güncellendi.", 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/class-schedules/{schedule}",
     *     tags={"Manager & Teacher - ClassSchedule"},
     *     summary="Ders planını sil",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Silindi")
     * )
     */
    public function destroy(School $school, ClassSchedule $schedule)
    {
        if (!auth()->user()->can('classschedule.delete',)) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        if ($schedule->school_id !== $school->id) {
            return $this->errorResponse("Bu okula ait değil.", 403);
        }

        $schedule->delete();

        return $this->successResponse([], "Ders planı silindi.", 200);
    }
}
