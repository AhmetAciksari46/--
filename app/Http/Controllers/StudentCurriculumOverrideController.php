<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\School\StoreStudentCurriculumOverrideRequest;
use App\Http\Requests\School\UpdateStudentCurriculumOverrideRequest;
use App\Models\StudentCurriculumOverride;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 * name="Curriculum Override",
 * description="Öğrenci Müfredat Geçersiz Kılma Kayıtları Yönetimi (Manager)"
 * )
 */
class StudentCurriculumOverrideController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     * path="/api/school/curriculum-overrides",
     * operationId="indexStudentCurriculumOverride",
     * tags={"Müfredat Yönetimi"},
     * summary="Tüm öğrenci müfredat geçersiz kılma kayıtlarını listeler.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Başarılı işlem",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/StudentCurriculumOverride")
     * )
     * ),
     * @OA\Response(response=403, description="Yetkisiz İşlem"),
     * )
     */
    public function index(Request $request)
    {
        // Manager sadece kendi okuluyla ilgili override'ları görmelidir.
        // Policy'de viewAny kontrol edildiği için burada filtreleme yapılır.
        $overrides = StudentCurriculumOverride::query();

        if (Auth::user()->hasRole('manager')) {
            // Basit bir filtreleme mekanizması (Örn: Manager'ın okuluyla ilişkili öğrencilerin override'ları)
            $schoolId = Auth::user()->school_id;
            $overrides->whereHas('student', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            });
        }
        return $this->successResponse($overrides->paginate($request->get('limit', 15)));
    }

    /**
     * @OA\Post(
     * path="/api/school/curriculum-overrides",
     * operationId="storeStudentCurriculumOverride",
     * tags={"Müfredat Yönetimi"},
     * summary="Yeni bir öğrenci müfredat geçersiz kılma kaydı oluşturur.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/StoreStudentCurriculumOverrideRequest")
     * ),
     * @OA\Response(
     * response=201,
     * description="Başarılı oluşturma",
     * @OA\JsonContent(ref="#/components/schemas/StudentCurriculumOverride")
     * ),
     * @OA\Response(response=403, description="Yetkisiz İşlem"),
     * @OA\Response(response=422, description="Doğrulama Hatası"),
     * )
     */
    public function store(StoreStudentCurriculumOverrideRequest $request)
    {
        // Not: Burada ayrıca Manager/Admin'in, belirtilen student_id'nin kendi okuluna ait olduğunu
        // kontrol etmesi gereken ek bir iş mantığı kontrolü olmalıdır (Policy'de de yapılabilir).

        $override = StudentCurriculumOverride::create($request->validated());
        return $this->successResponse($override, 201);
    }

    /**
     * @OA\Get(
     * path="/api/school/curriculum-overrides/{id}",
     * operationId="showStudentCurriculumOverride",
     * tags={"Müfredat Yönetimi"},
     * summary="Belirli bir müfredat geçersiz kılma kaydını gösterir.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Geçersiz kılma kaydı ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Başarılı işlem",
     * @OA\JsonContent(ref="#/components/schemas/StudentCurriculumOverride")
     * ),
     * @OA\Response(response=403, description="Yetkisiz İşlem"),
     * @OA\Response(response=404, description="Kayıt bulunamadı"),
     * )
     */
    public function show(StudentCurriculumOverride $override)
    {
        return $this->successResponse($override);
    }

    /**
     * @OA\Put(
     * path="/api/school/curriculum-overrides/{id}",
     * operationId="updateStudentCurriculumOverride",
     * tags={"Müfredat Yönetimi"},
     * summary="Belirli bir müfredat geçersiz kılma kaydını günceller.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Geçersiz kılma kaydı ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/UpdateStudentCurriculumOverrideRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Başarılı güncelleme",
     * @OA\JsonContent(ref="#/components/schemas/StudentCurriculumOverride")
     * ),
     * @OA\Response(response=403, description="Yetkisiz İşlem"),
     * @OA\Response(response=404, description="Kayıt bulunamadı"),
     * @OA\Response(response=422, description="Doğrulama Hatası"),
     * )
     */
    public function update(UpdateStudentCurriculumOverrideRequest $request, StudentCurriculumOverride $override)
    {
        $override->update($request->validated());
        return $this->successResponse($override);
    }

    /**
     * @OA\Delete(
     * path="/api/school/curriculum-overrides/{id}",
     * operationId="destroyStudentCurriculumOverride",
     * tags={"Müfredat Yönetimi"},
     * summary="Belirli bir müfredat geçersiz kılma kaydını siler.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="Geçersiz kılma kaydı ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Başarılı silme (İçerik Yok)",
     * ),
     * @OA\Response(response=403, description="Yetkisiz İşlem"),
     * @OA\Response(response=404, description="Kayıt bulunamadı"),
     * )
     */
    public function destroy(StudentCurriculumOverride $override)
    {
        $override->delete();
        return $this->successResponse(null, null, 204);
    }
}
