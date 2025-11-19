<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\SchoolWeek;
use App\Models\SchoolWeekDay;
use App\Http\Requests\School\Week\StoreSchoolWeekDayRequest;
use App\Http\Requests\School\Week\UpdateSchoolWeekDayRequest;
use App\Http\Requests\School\Week\AutoGenerateWeekDaysRequest;
use Carbon\Carbon;

/**
 * @OA\Tag(
 * name="Manager School Week Days",
 * description="Okul Günleri ve Haftaları Yönetimi "
 * )
 */
class SchoolWeekDayController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks/{week}/days",
     *     summary="Bir haftaya ait tüm günleri listeler",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),  
     *     @OA\Response(response=200, description="Günler listelendi.")
     * )
     */
    public function index(School $school, SchoolWeek $week)
    {
        return $this->successResponse(
            $week->days,
            "Günler başarıyla listelendi."
        );
    }

    /**
     * @OA\Post(
     *     path="/api/schools/{school}/weeks/{week}/days",
     *     summary="Haftaya yeni bir ders günü ekler",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),     
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/StoreSchoolWeekDayRequest")),
     *     @OA\Response(response=201, description="Gün oluşturuldu.")
     * )
     */
    public function store(StoreSchoolWeekDayRequest $request, School $school, SchoolWeek $week)
    {
        $day = $week->days()->create($request->validated());

        return $this->successResponse($day, "Gün başarıyla oluşturuldu.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks/{week}/days/{day}",
     *     summary="Belirli bir günün detaylarını getirir",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),     
     *     @OA\Parameter(name="day", in="path", required=true),
     *     @OA\Response(response=200, description="Gün detayları getirildi.")
     * )
     */
    public function show(School $school, SchoolWeek $week, SchoolWeekDay $day)
    {
        return $this->successResponse(
            $day,
            "Gün detayları getirildi."
        );
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/weeks/{week}/days/{day}",
     *     summary="Günü günceller",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),     
     *     @OA\Parameter(name="day", in="path", required=true),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpdateSchoolWeekDayRequest")),
     *     @OA\Response(response=200, description="Gün güncellendi.")
     * )
     */
    public function update(UpdateSchoolWeekDayRequest $request, School $school, SchoolWeek $week, SchoolWeekDay $day)
    {
        $day->update($request->validated());

        return $this->successResponse($day, "Gün başarıyla güncellendi.");
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/weeks/{week}/days/{day}",
     *     summary="Günü siler",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),     
     *     @OA\Parameter(name="day", in="path", required=true),
     *     @OA\Response(response=200, description="Gün silindi.")
     * )
     */
    public function destroy(School $school, SchoolWeek $week, SchoolWeekDay $day)
    {
        $day->delete();

        return $this->successResponse(null, "Gün başarıyla silindi.");
    }


    /**
     * @OA\Post(
     *     path="/api/schools/{school}/weeks/{week}/days/auto-generate",
     *     summary="Days of week bazlı günleri otomatik oluşturur",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="school", in="path", required=true),
     *     @OA\Parameter(name="week", in="path", required=true),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AutoGenerateWeekDaysRequest")),
     *
     *     @OA\Response(response=200, description="Günler başarıyla otomatik oluşturuldu.")
     * )
     */
    public function autoGenerate(AutoGenerateWeekDaysRequest $request, School $school, SchoolWeek $week)
    {
        // Eski günleri temizle
        $week->days()->delete();

        $start = Carbon::parse($week->start_date);
        $dayNo = 1;
        $generated = [];

        foreach ($request->days_of_week as $dow) {

            $realDate = $start->copy()->next($dow);

            $day = $week->days()->create([
                'day_no'   => $dayNo++,
                'real_date' => $realDate
            ]);

            $generated[] = $day;
        }

        return $this->successResponse($generated, "Günler başarıyla otomatik oluşturuldu.");
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks/{week}/days/check",
     *     summary="Bir haftanın günlerinin tamamlama durumunu kontrol eder",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="school",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="week",
     *         in="path",
     *         required=true,
     *         description="Hafta ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Gün tamamlama durumu döner"
     *     )
     * )
     */
    public function checkDays(School $school, SchoolWeek $week)
    {
        // 1) Bu haftanın kural kaydı
        $rule = $week->rule;

        if (!$rule) {
            return response()->json([
                'message' => 'Bu haftaya bağlı bir paket hafta kuralı bulunamadı.'
            ], 422);
        }

        $expected = (int) $rule->days_required;
        $actual = $week->days()->count();

        $isComplete = $actual >= $expected;

        // Fazla tanımlanmışsa da bunu gösterelim
        $status = $isComplete
            ? ($actual == $expected ? 'tam' : 'fazla_gun_var')
            : 'eksik_gun_var';

        $response = [
            'week_id' => $week->id,
            'grade'   => $rule->grade,
            'week_no' => $rule->week_no,
            'days_required' => $expected,
            'days_created'  => $actual,
            'is_complete'   => $isComplete,
            'status'        => $status, // frontend buna göre "Henüz tamamlamadınız!" vs gösterebilir
        ];

        return $this->successResponse($response, 'Gün tamamlama durumu hesaplandı.');
    }
}
