<?php

namespace App\Http\Controllers;

use App\Models\PackageWeekGradeRule;
use Illuminate\Http\Request;
use App\Http\Requests\Curriculum\StorePackageWeekGradeRuleRequest;
use App\Http\Requests\Curriculum\UpdatePackageWeekGradeRuleRequest;
use App\Traits\ApiResponser;
use App\Models\School;

/**
 * @OA\Tag(
 * name="Package Grade Rules",
 * description="Paket Hafta Derece Kuralı Yönetimi (Admin/Manager)"
 * )
 */
class PackageWeekGradeRuleController extends Controller
{
    use ApiResponser;
    //TODO:ERROR MESAJLARI İÇİN TRAIT OLUŞTURULACAK


    // --- CRUD METOTLARI ---
    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/grade-rules",
     * operationId="listPackageWeekGradeRules",
     * tags={"Package Grade Rules"},
     * summary="Okulun kullandığı pakete ait tüm derece kurallarını listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PackageWeekGradeRule"))),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school)
    {
        // Policy: Manager, kendi okulunun paketiyle ilgili kuralları görebilir.
        $this->authorize('viewAny', PackageWeekGradeRule::class, $school);

        $packageId = $school->package_id;

        $rules = PackageWeekGradeRule::where('package_id', $packageId)
            ->orderBy('week_no')
            ->get();
        return $this->successResponse($rules);
    }
    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/grade-rules/{rule}",
     * operationId="showPackageWeekGradeRule",
     * tags={"Package Grade Rules"},
     * summary="Belirli bir derece kuralını detayını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekGradeRule")),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function show(School $school, PackageWeekGradeRule $rule)
    {
        $this->authorize('view', $rule);

        // Ek Kontrol: Kuralın, URL'deki okulun paketine ait olup olmadığı
        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Kural, belirtilen okulun paketine ait değil.'], 404);
        }
        return $this->successResponse($rule);
    }

    /**
     * @OA\Post(
     * path="/api/schools/{school}/manager/grade-rules",
     * operationId="storePackageWeekGradeRule",
     * tags={"Package Grade Rules"},
     * summary="Paket için yeni bir derece kuralı oluşturur",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePackageWeekGradeRuleRequest")),
     * @OA\Response(response=201, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekGradeRule")),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */

    public function store(StorePackageWeekGradeRuleRequest $request, School $school)
    {
        $this->authorize('create', PackageWeekGradeRule::class);

        $data = $request->validated();

        // Paket ID'sini Request'ten alıyoruz.
        // NOT: Kurallar pakete atanır. Request'te gelen package_id, Manager'ın okulunun package_id'si olmalıdır!
        if ($data['package_id'] !== $school->package_id) {
            return response()->json(['message' => 'Oluşturulacak kural, yöneticisi olduğunuz okulun paketine ait olmalıdır.'], 403);
        }

        $rule = PackageWeekGradeRule::create($data);
        return $this->successResponse($rule, null, 201);
    }
    /**
     * @OA\Put(
     * path="/api/schools/{school}/manager/grade-rules/{rule}",
     * operationId="updatePackageWeekGradeRule",
     * tags={"Package Grade Rules"},
     * summary="Belirli bir derece kuralını günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdatePackageWeekGradeRuleRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekGradeRule")),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function update(UpdatePackageWeekGradeRuleRequest $request, School $school, PackageWeekGradeRule $rule)
    {
        $this->authorize('update', $rule);

        // Ek Kontrol
        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Güncellenecek kural, belirtilen okulun paketine ait değil.'], 404);
        }

        $rule->update($request->validated());
        return $this->successResponse($rule);
    }
    /**
     * @OA\Delete(
     * path="/api/schools/{school}/manager/grade-rules/{rule}",
     * operationId="deletePackageWeekGradeRule",
     * tags={"Package Grade Rules"},
     * summary="Belirli bir derece kuralını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function destroy(School $school, PackageWeekGradeRule $rule)
    {
        $this->authorize('delete', $rule);

        // Ek Kontrol
        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Silinecek kural, belirtilen okulun paketine ait değil.'], 404);
        }

        $rule->delete();
        return $this->successResponse(null, 204);
    }
}
