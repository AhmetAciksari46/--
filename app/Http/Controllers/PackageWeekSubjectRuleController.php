<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PackageWeekSubjectRule;
use App\Http\Requests\Curriculum\StorePackageWeekSubjectRuleRequest;
use App\Http\Requests\Curriculum\UpdatePackageWeekSubjectRuleRequest;
use App\Traits\ApiResponser;
use App\Models\School;
use App\Models\Package;

/**
 * @OA\Tag(name="Admin Package Rules", description="Paketlere Sınıf ve Ders Kuralı Ekleme/Yönetimi")
 */
class PackageWeekSubjectRuleController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     * path="/api/admin/packages/{package}/subject-rules",
     * summary="Paketin Ders Kurallarını Listeleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Response(response=200, description="Ders kuralları listelendi.")
     * )
     */
    public function index(Package $package)
    {
        // İlişkili GradeRule'lar üzerinden SubjectRule'ları getiriyoruz.
        $subjectRules = PackageWeekSubjectRule::whereHas('gradeRule', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->with('gradeRule.grade', 'subject')->get();
        return $this->successResponse('Ders kuralları listelendi.', $subjectRules);
    }

    /**
     * @OA\Post(
     * path="/api/admin/packages/{package}/subject-rules",
     * summary="Pakete Yeni Ders Kuralı Ekleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePackageWeekSubjectRuleRequest")),
     * @OA\Response(response=201, description="Ders kuralı başarıyla eklendi.")
     * )
     */
    public function store(StorePackageWeekSubjectRuleRequest $request, Package $package)
    {
        // StorePackageWeekSubjectRuleRequest içinde grade_rule_id'nin bu pakete ait olduğu kontrol ediliyor.
        $rule = PackageWeekSubjectRule::create($request->validated());
        return $this->successResponse(null, 201, 'Ders kuralı başarıyla eklendi.');
    }

    /**
     * @OA\Put(
     * path="/api/admin/packages/{package}/subject-rules/{rule}",
     * summary="Paket Ders Kuralını Güncelleme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Kural başarıyla güncellendi.")
     * )
     */
    public function update(UpdatePackageWeekSubjectRuleRequest $request, Package $package, PackageWeekSubjectRule $subject_rule)
    {
        // Kuralın gerçekten bu pakete ait olup olmadığını kontrol et
        if ($subject_rule->gradeRule->package_id !== $package->id) {
            return $this->errorResponse('Kural bu pakete ait değil.', 404);
        }

        $subject_rule->update($request->validated());
        return $this->successResponse('Ders kuralı başarıyla güncellendi.');
    }

    /**
     * @OA\Delete(
     * path="/api/admin/packages/{package}/subject-rules/{rule}",
     * summary="Paket Ders Kuralını Silme",
     * tags={"Admin Package Rules"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Kural başarıyla silindi.")
     * )
     */
    public function destroy(Package $package, PackageWeekSubjectRule $subject_rule)
    {
        if ($subject_rule->gradeRule->package_id !== $package->id) {
            return $this->errorResponse('Kural bu pakete ait değil.', 404);
        }

        $subject_rule->delete();
        return $this->successResponse('Ders kuralı başarıyla silindi.');
    }
}
