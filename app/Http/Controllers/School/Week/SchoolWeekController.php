<?php

namespace App\Http\Controllers\School\Week;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolWeek;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Http\Requests\School\Week\StoreSchoolWeekRequest;
use App\Http\Requests\School\Week\UpdateSchoolWeekRequest;
use App\Models\Grade;
use App\Models\PackageWeekGradeRule;
use Carbon\Carbon;

/**
 * @OA\Tag(
 * name="Manager School Week Days",
 * description="Okul Günleri ve Haftaları Yönetimi "
 * )
 */
class SchoolWeekController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks",
     *     summary="Okuldaki tüm haftaları listeler",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Haftalar listelendi")
     * )
     */
    public function index(School $school)
    {
        $weeks = SchoolWeek::with(['rule', 'days'])
            ->where('school_id', $school->id)
            ->get();

        return $this->successResponse($weeks, "Haftalar başarıyla listelendi.");
    }
    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks/check",
     *     summary="Seçilen grade için haftaların tamamlanma durumunu kontrol eder",
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
     *         name="grade",
     *         in="query",
     *         required=true,
     *         description="Kontrol edilecek grade (sınıf) ID veya numarası",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Hafta tamamlama durumu döner"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Aktif paket yok veya grade için kural yok"
     *     )
     * )
     */
    public function checkWeeks(Request $request, School $school)
    {
        $request->validate([
            'grade' => 'required|integer',
        ], [
            'grade.required' => 'Grade (sınıf) değeri zorunludur.',
            'grade.integer'  => 'Grade (sınıf) değeri sayısal olmalıdır.',
        ]);

        $grade = $request->grade;

        // 1) Okulun aktif paketini al
        $package = $school->activePackage();

        if (!$package) {
            return response()->json([
                'message' => 'Bu okulun aktif bir paketi bulunamadı.'
            ], 422);
        }

        // 2) Bu paket + grade için tüm hafta kuralları
        $rules = PackageWeekGradeRule::where('package_id', $package->id)
            ->where('grade', $grade)
            ->orderBy('week_no')
            ->get();

        if ($rules->isEmpty()) {
            return response()->json([
                'message' => 'Bu paket ve grade için tanımlı hafta kuralı bulunamadı.'
            ], 422);
        }

        $totalRequiredWeeks = $rules->count();
        $ruleIds = $rules->pluck('id');
        $ruleWeekNumbers = $rules->pluck('week_no', 'id'); // id => week_no map

        // 3) Okulun oluşturduğu SchoolWeek kayıtları
        $weeks = SchoolWeek::where('school_id', $school->id)
            ->whereIn('package_week_grade_rule_id', $ruleIds)
            ->get();

        $createdRuleIds = $weeks->pluck('package_week_grade_rule_id');

        // 4) Eksik olan kural id'leri
        $missingRuleIds = $ruleIds->diff($createdRuleIds);

        // Eksik week_no’ları çıkar
        $missingWeekNumbers = $missingRuleIds->map(function ($id) use ($ruleWeekNumbers) {
            return $ruleWeekNumbers[$id] ?? null;
        })->filter()->values()->all();

        $response = [
            'grade' => (int)$grade,
            'package_id' => $package->id,
            'total_weeks_required' => $totalRequiredWeeks,
            'weeks_created' => $weeks->count(),
            'is_complete' => $missingRuleIds->isEmpty(),
            'missing_weeks' => $missingWeekNumbers, // örn: [3, 27]
        ];

        return $this->successResponse($response, 'Hafta tamamlama durumu hesaplandı.');
    }


    /**
     * @OA\Post(
     *     path="/api/schools/{school}/weeks/{rule}/auto-generate",
     *     summary="Tüm haftaları otomatik oluşturur",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreSchoolWeekRequest")),
     *     @OA\Response(response=200, description="Haftalar oluşturuldu.")
     * )
     */
    public function autoGenerate(StoreSchoolWeekRequest $request, School $school, Grade $grade)
    {
        $rules = PackageWeekGradeRule::where('grade', $grade->id)->orderBy('week_no')->get();

        $currentStart = Carbon::parse($request->start_date);

        foreach ($rules as $rule) {
            SchoolWeek::updateOrCreate([
                'school_id' => $school->id,
                'package_week_grade_rule_id' => $rule->id,
            ], [
                'start_date' => $currentStart->copy()
            ]);

            $currentStart->addDays(7);
        }

        return $this->successResponse(null, "Haftalar otomatik oluşturuldu.");
    }
    /**
     * @OA\Post(
     *     path="/api/schools/{school}/weeks",
     *     summary="Yeni okul haftası oluşturur",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/StoreSchoolWeekRequest")),
     *     @OA\Response(response=201, description="Hafta oluşturuldu.")
     * )
     */
    public function store(StoreSchoolWeekRequest $request, School $school)
    {
        $week = SchoolWeek::create([
            'school_id' => $school->id,
            'package_week_grade_rule_id' => $request->package_week_grade_rule_id,
            'start_date' => $request->start_date,
        ]);

        return $this->successResponse($week, "Hafta başarıyla oluşturuldu.", 201);
    }

    /**
     * @OA\Get(
     *     path="/api/schools/{school}/weeks/{week}",
     *     summary="Belirli bir haftayı getirir",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Hafta getirildi.")
     * )
     */
    public function show(School $school, SchoolWeek $week)
    {
        return $this->successResponse(
            $week->load(['rule', 'days']),
            "Hafta detayları getirildi."
        );
    }

    /**
     * @OA\Put(
     *     path="/api/schools/{school}/weeks/{week}",
     *     summary="Haftayı günceller",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UpdateSchoolWeekRequest")),
     *     @OA\Response(response=200, description="Hafta güncellendi.")
     * )
     */
    public function update(UpdateSchoolWeekRequest $request, School $school, SchoolWeek $week)
    {
        $week->update($request->validated());

        return $this->successResponse($week, "Hafta başarıyla güncellendi.");
    }

    /**
     * @OA\Delete(
     *     path="/api/schools/{school}/weeks/{week}",
     *     summary="Haftayı siler",
     *     tags={"Manager School Week Days"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Hafta silindi.")
     * )
     */
    public function destroy(School $school, SchoolWeek $week)
    {
        $week->delete();

        return $this->successResponse(null, "Hafta başarıyla silindi.");
    }
}
