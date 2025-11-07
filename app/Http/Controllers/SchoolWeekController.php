<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolWeek;
use App\Traits\ApiResponser;
use App\Http\Requests\School\UpdateSchoolWeekRequest;
use App\Models\School;
use App\Http\Requests\School\StoreSchoolWeekRequest;

/**
 * @OA\Tag(
 * name="School Week Management",
 * description="Okul Haftalık Takvim (SchoolWeek) Yönetimi (Manager)"
 * )
 */
class SchoolWeekController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/weeks",
     * operationId="listSchoolWeeks",
     * tags={"School Week Management"},
     * summary="Belirtilen okuldaki tüm haftaları listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Response(
     * response=200, 
     * description="Başarılı", 
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SchoolWeek"))
     * ),
     * @OA\Response(response=404, description="Okul Bulunamadı")
     * )
     */
    public function index(School $school)
    {
        $this->authorize('viewAny', SchoolWeek::class, $school);

        $weeks = $school->weeks()->orderBy('week_no')->get();
        return $this->successResponse($weeks);
    }

    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/weeks/{week}",
     * operationId="showSchoolWeek",
     * tags={"School Week Management"},
     * summary="Belirli bir okul haftasının detaylarını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="week", in="path", required=true, @OA\Schema(type="integer"), description="SchoolWeek ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolWeek")),
     * @OA\Response(response=404, description="Hafta bulunamadı")
     * )
     */
    public function show(School $school, SchoolWeek $week)
    {
        $this->authorize('view', $week);

        if ($week->school_id !== $school->id) {
            return response()->json(['message' => 'Hafta kaydı belirtilen okula ait değil.'], 404);
        }
        return $this->successResponse($week);
    }

    /**
     * @OA\Post(
     * path="/api/schools/{school}/manager/weeks",
     * operationId="storeSchoolWeek",
     * tags={"School Week Management"},
     * summary="Yeni bir okul haftası kaydı oluşturur",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreSchoolWeekRequest")),
     * @OA\Response(response=201, description="Başarılı oluşturma", @OA\JsonContent(ref="#/components/schemas/SchoolWeek")),
     * @OA\Response(response=422, description="Doğrulama Hatası")
     * )
     */
    public function store(StoreSchoolWeekRequest $request, School $school)
    {
        $this->authorize('create', SchoolWeek::class);

        $week = $school->weeks()->create($request->validated());
        return $this->successResponse($week, 201);
    }

    /**
     * @OA\Put(
     * path="/api/schools/{school}/manager/weeks/{week}",
     * operationId="updateSchoolWeek",
     * tags={"School Week Management"},
     * summary="Belirli bir okul haftasını günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="week", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateSchoolWeekRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolWeek")),
     * @OA\Response(response=404, description="Hafta bulunamadı")
     * )
     */
    public function update(UpdateSchoolWeekRequest $request, School $school, SchoolWeek $week)
    {
        $this->authorize('update', $week);

        if ($week->school_id !== $school->id) {
            return response()->json(['message' => 'Hafta kaydı belirtilen okula ait değil.'], 404);
        }

        $week->update($request->validated());
        return response()->json($week);
    }

    /**
     * @OA\Delete(
     * path="/api/schools/{school}/manager/weeks/{week}",
     * operationId="deleteSchoolWeek",
     * tags={"School Week Management"},
     * summary="Belirli bir okul haftasını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="week", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Hafta bulunamadı")
     * )
     */
    public function destroy(School $school, SchoolWeek $week)
    {
        $this->authorize('delete', $week);

        if ($week->school_id !== $school->id) {
            return response()->json(['message' => 'Hafta kaydı belirtilen okula ait değil.'], 404);
        }

        $week->delete();
        return response()->json(null, 204);
    }
}
