<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PackageWeekSubjectRule;
use App\Http\Requests\Curriculum\StorePackageWeekSubjectRuleRequest;
use App\Http\Requests\Curriculum\UpdatePackageWeekSubjectRuleRequest;
use App\Traits\ApiResponser;
use App\Models\School;

/**
 * @OA\Tag(
 * name="Package Subject Rules",
 * description="Paket Hafta Ders Kuralı Yönetimi (Admin/Manager)"
 * )
 */
class PackageWeekSubjectRuleController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Post(
     * path="/api/schools/{school}/manager/subject-rules",
     * operationId="storePackageWeekSubjectRule",
     * tags={"Package Subject Rules"},
     * summary="Paket için yeni bir ders kuralı oluşturur",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePackageWeekSubjectRuleRequest")),
     * @OA\Response(response=201, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekSubjectRule")),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function store(StorePackageWeekSubjectRuleRequest $request, School $school)
    {
        // Policy: create yetkisi
        $this->authorize('create', PackageWeekSubjectRule::class);

        $data = $request->validated();
        $rule = PackageWeekSubjectRule::create($data);
        return $this->successResponse($rule, null, 201);
    }

    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/subject-rules",
     * operationId="listPackageWeekSubjectRules",
     * tags={"Package Subject Rules"},
     * summary="Okulun kullandığı pakete ait tüm ders kurallarını listeler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PackageWeekSubjectRule"))),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index(School $school)
    {
        // Policy: Manager, kendi okulunun paketiyle ilgili kuralları görebilir.
        $this->authorize('viewAny', PackageWeekSubjectRule::class, $school);

        $packageId = $school->package_id;

        $rules = PackageWeekSubjectRule::where('package_id', $packageId)
            ->orderBy('week_no')
            ->get();
        return $this->successResponse($rules);
    }

    /**
     * @OA\Get(
     * path="/api/schools/{school}/manager/subject-rules/{rule}",
     * operationId="showPackageWeekSubjectRule",
     * tags={"Package Subject Rules"},
     * summary="Belirli bir ders kuralını detayını getirir",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekSubjectRule")),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function show(School $school, PackageWeekSubjectRule $rule)
    {
        $this->authorize('view', $rule);

        // Ek Kontrol: Kuralın, URL'deki okulun paketine ait olup olmadığı
        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Kural, belirtilen okulun paketine ait değil.'], 404);
        }
        return $this->successResponse($rule);
    }
    /**
     * @OA\Put(
     * path="/api/schools/{school}/manager/subject-rules/{rule}",
     * operationId="updatePackageWeekSubjectRule",
     * tags={"Package Subject Rules"},
     * summary="Belirli bir ders kuralını günceller",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdatePackageWeekSubjectRuleRequest")),
     * @OA\Response(response=200, description="Başarılı", @OA\JsonContent(ref="#/components/schemas/PackageWeekSubjectRule")),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function update(UpdatePackageWeekSubjectRuleRequest $request, School $school, PackageWeekSubjectRule $rule)
    {
        $this->authorize('update', $rule);

        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Güncellenecek kural, belirtilen okulun paketine ait değil.'], 404);
        }

        $rule->update($request->validated());
        return $this->successResponse($rule);
    }
    /**
     * @OA\Delete(
     * path="/api/schools/{school}/manager/subject-rules/{rule}",
     * operationId="deletePackageWeekSubjectRule",
     * tags={"Package Subject Rules"},
     * summary="Belirli bir ders kuralını siler",
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="school", in="path", required=true, @OA\Schema(type="integer"), description="Okul ID"),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=204, description="Başarılı (İçerik Yok)"),
     * @OA\Response(response=404, description="Kural bulunamadı veya okula ait değil")
     * )
     */
    public function destroy(School $school, PackageWeekSubjectRule $rule)
    {
        $this->authorize('delete', $rule);

        if ($rule->package_id !== $school->package_id) {
            return response()->json(['message' => 'Silinecek kural, belirtilen okulun paketine ait değil.'], 404);
        }

        $rule->delete();
        return $this->successResponse(null, 204);
    }



    // TODO:... Diğer CRUD metotları
}
