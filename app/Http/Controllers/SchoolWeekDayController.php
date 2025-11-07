<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolWeekDay;
use App\Http\Requests\School\UpdateSchoolDayRequest;
use App\Http\Requests\School\StoreSchoolDayRequest; // Yeni
use App\Traits\ApiResponser;

/**
 * @OA\Tag(
 * name="School Day Management",
 * description="Okul Günleri (SchoolDay) Yönetimi (Manager)"
 * )
 */
class SchoolWeekDayController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/days",
     * operationId="listSchoolDays",
     * tags={"School Day Management"},
     * summary="Okulun haftalık açık günlerini listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="week", in="path", required=true, @OA\Schema(type="integer"), description="SchoolWeek ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SchoolDay"))),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school)
    {
        // Policy: viewAny yetkisi
        $this->authorize('viewAny', SchoolWeekDay::class, $school);

        $days = $school->days()
            ->orderBy('day_of_week_no')
            ->get();
        return $this->successResponse($days);
    }

    /**
     * @OA\Put(
     * path="/api/schools/{school}/manager/days/{day}",
     * operationId="updateSchoolDay",
     * tags={"School Day Management"},
     * summary="Belirli bir günü günceller (Örn: Açık/Kapalı Ayarı)",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="day", in="path", required=true, @OA\Schema(type="integer"), description="SchoolDay ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateSchoolDayRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolDay")),
     * @OA\Response(response=404, description="Kayıt Bulunamadı"),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function update(UpdateSchoolDayRequest $request, School $school, SchoolWeekDay $day)
    {
        // Policy: update yetkisi
        $this->authorize('update', $day);

        // Ek kontrol: SchoolDay'in URL'deki School'a ait olduğundan emin ol.
        if ($day->school_id !== $school->id) {
            return $this->errorResponse('Gün kaydı belirtilen okula ait değil.', 404);
        }

        $day->update($request->validated());

        return response()->json($day);
        return $this->successResponse($day);
    }


    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/days/{day}",
     * operationId="showSchoolDay",
     * tags={"School Day Management"},
     * summary="Belirli bir okul gününün detayını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="day", in="path", required=true, @OA\Schema(type="integer"), description="SchoolDay ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolDay")),
     * @OA\Response(response=404, description="Kayıt Bulunamadı veya Okula Ait Değil")
     * )
     */
    public function show(School $school, SchoolWeekDay $day)
    {
        $this->authorize('view', $day);

        if ($day->school_id !== $school->id) {
            return response()->json(['message' => 'Gün kaydı belirtilen okula ait değil.'], 404);
        }
        return $this->successResponse($day);
    }

    /**
     * @OA\Post(
     * path="/api/schools/{school}/manager/days",
     * operationId="storeSchoolDay",
     * tags={"School Day Management"},
     * summary="Yeni bir okul günü kaydı oluşturur (Örn: Ek bir gün)",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreSchoolDayRequest")),
     * @OA\Response(response=201, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/SchoolDay")),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function store(StoreSchoolDayRequest $request, School $school)
    {
        $this->authorize('create', SchoolWeekDay::class);

        $day = $school->days()->create($request->validated());
        return $this->successResponse($day, 201);
    }

    // ... update metodu aynı (CRUD tamamlandıktan sonra üstte yer alır)

    /**
     * @OA\Delete(
     * path="/api/schools/{school}/manager/days/{day}",
     * operationId="deleteSchoolDay",
     * tags={"School Day Management"},
     * summary="Belirli bir okul günü kaydını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="day", in="path", required=true, @OA\Schema(type="integer"), description="SchoolDay ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Kayıt Bulunamadı veya Okula Ait Değil")
     * )
     */
    public function destroy(School $school, SchoolWeekDay $day)
    {
        $this->authorize('delete', $day);

        if ($day->school_id !== $school->id) {
            return response()->json(['message' => 'Gün kaydı belirtilen okula ait değil.'], 404);
        }

        $day->delete();
        return $this->successResponse(null, 204);
    }
}
