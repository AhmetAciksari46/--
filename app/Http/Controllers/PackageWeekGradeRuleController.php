<?php

namespace App\Http\Controllers;

use App\Models\PackageWeekGradeRule;
use Illuminate\Http\Request;
use App\Http\Requests\Curriculum\StorePackageWeekGradeRuleRequest;
use App\Http\Requests\Curriculum\UpdatePackageWeekGradeRuleRequest;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\Package;

/**
 * @OA\Tag(name="Admin Package Rules", description="Paketlere Sınıf ve Ders Kuralı Ekleme/Yönetimi")
 */
class PackageWeekGradeRuleController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     * path="/api/admin/packages/{package}/grade-rules",
     * summary="Paketin Sınıf Kurallarını Listeleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Response(response=200, description="Sınıf kuralları listelendi.")
     * )
     */
    public function index(Package $package)
    {
        return $this->successResponse($package->gradeRules()->with('grade')->get());
    }

    /**
     * @OA\Post(
     * path="/api/admin/packages/{package}/grade-rules",
     * summary="Pakete Yeni Sınıf Kuralı Ekleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePackageWeekGradeRuleRequest")),
     * @OA\Response(response=201, description="Kural başarıyla eklendi.")
     * )
     */
    public function store(StorePackageWeekGradeRuleRequest $request, Package $package)
    {
        $rule = $package->gradeRules()->create($request->validated());

        return $this->successResponse($rule->load('grade'), "Sınıf kuralı başarıyla eklendi", 201);
    }

    /**
     * @OA\Put(
     * path="/api/admin/packages/{package}/grade-rules/{rule}",
     * summary="Paket Sınıf Kuralını Güncelleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Kural başarıyla güncellendi.")
     * )
     */
    public function update(UpdatePackageWeekGradeRuleRequest $request, Package $package, PackageWeekGradeRule $grade_rule)
    {
        // Route Model Binding ile gelen kuralın gerçekten bu pakete ait olup olmadığını kontrol et
        if ($grade_rule->package_id !== $package->id) {
            return $this->errorResponse('Kural bu pakete ait değil.', 404);
        }

        $grade_rule->update($request->validated());
        return $this->successResponse($grade_rule->load('grade'), 'Sınıf kuralı başarıyla güncellendi.');
    }

    /**
     * @OA\Delete(
     * path="/api/admin/packages/{package}/grade-rules/{rule}",
     * summary="Paket Sınıf Kuralını Silme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Kural başarıyla silindi.")
     * )
     */
    public function destroy(Package $package, PackageWeekGradeRule $grade_rule)
    {
        if ($grade_rule->package_id !== $package->id) {
            return $this->errorResponse('Kural bu pakete ait değil.', 404);
        }

        $grade_rule->delete();

        return $this->successResponse(null, 'Sınıf kuralı başarıyla silindi.');
    }
}
