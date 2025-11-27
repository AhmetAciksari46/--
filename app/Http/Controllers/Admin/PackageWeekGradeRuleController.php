<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\PackageWeekGradeRule;
use App\Http\Requests\Curriculum\StorePackageWeekGradeRuleRequest;
use App\Http\Requests\Curriculum\UpdatePackageWeekGradeRuleRequest;
use App\Traits\ApiResponser;
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
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Response(response=200, description="Sınıf kuralları listelendi.")
     * )
     */
    public function index(Package $package)
    {
        return $this->successResponse($package->gradeRules, 'Sınıf kuralları listelendi.');
    }
    /**
     * @OA\Get(
     * path="/api/admin/packages/{package}/grade-rules/{grade_rule}",
     * summary="Paketin Sınıf Kurallarını Listeleme",
     * tags={"Admin Package Rules"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Parameter(name="grade_rule", in="path", required=true, @OA\Schema(type="integer"), description="grade_rule ID"),
     * @OA\Response(response=200, description="Kural getirildi.")
     * )
     */
    public function show(Package $package, PackageWeekGradeRule $grade_rule)
    {
        $grade_details = $package->gradeRules()->where('id', $grade_rule->id)->first();
        return $this->successResponse($grade_details, 'Kural getirildi');
    }


    /**
     * @OA\Post(
     * path="/api/admin/packages/{package}/grade-rules",
     * summary="Pakete Yeni Sınıf Kuralı Ekleme",
     * tags={"Admin Package Rules"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePackageWeekGradeRuleRequest")),
     * @OA\Response(response=201, description="Kural başarıyla eklendi.")
     * )
     */
    public function store(StorePackageWeekGradeRuleRequest $request, Package $package)
    {
        $data = $request->validated();
        $data['package_id'] = $package->id;

        $rule = PackageWeekGradeRule::create($data);

        return $this->successResponse($rule, 'Sınıf kuralı başarıyla eklendi.', 201);
    }

    /**
     * @OA\Put(
     * path="/api/admin/packages/{package}/grade-rules/{rule}",
     * summary="Paket Sınıf Kuralını Güncelleme",
     * tags={"Admin Package Rules"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdatePackageWeekGradeRuleRequest")),
     * @OA\Response(response=200, description="Kural başarıyla güncellendi.")
     * )
     */
    public function update(UpdatePackageWeekGradeRuleRequest $request, Package $package, PackageWeekGradeRule $grade_rule)
    {
        if ($grade_rule->package_id !== $package->id) {
            return $this->errorResponse('not_found', 404);
        }

        $grade_rule->update($request->validated());

        $grade_rule->update($request->validated());
        return $this->successResponse($grade_rule->refresh(), 'Sınıf kuralı başarıyla güncellendi.');
    }

    /**
     * @OA\Delete(
     * path="/api/admin/packages/{package}/grade-rules/{rule}",
     * summary="Paket Sınıf Kuralını Silme",
     * tags={"Admin Package Rules"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="rule", in="path", required=true, @OA\Schema(type="integer"), description="Kural ID"),
     * @OA\Response(response=200, description="Kural başarıyla silindi.")
     * )
     */
    public function destroy(Package $package, PackageWeekGradeRule $grade_rule)
    {
        if ($grade_rule->package_id !== $package->id) {
            return $this->errorResponse('not_found', 404);
        }

        $grade_rule->delete();

        return $this->successResponse(null, 'Sınıf kuralı başarıyla silindi.');
    }



    /**
     * @OA\Get(
     *     path="/api/manager/packages/{package}/my-grade-rules",
     *     summary="Manager'ın bağlı olduğu okulun paketini görüntüleme",
     *     tags={"Manager Genel İşlemleri"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="package",
     *         in="path",
     *         required=true,
     *         description="Paket ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Response(response=200, description="Paket bilgisi getirildi."),
     *     @OA\Response(response=403, description="Bu pakete erişim izniniz yok."),
     *     @OA\Response(response=404, description="Paket bulunamadı.")
     * )
     */
    public function showManagerPackage(Package $package)
    {
        $user = auth()->user();
        // 🔐 Rol Doğrulama
        if (!$user->hasRole('manager')) {
            return response()->json(['message' => 'Bu işlemi yapmak için yetkiniz yok.'], 403);
        }

        // 🏫 Manager'ın okulu
        $school = $user->managerProfile->school ?? null;

        if (!$school) {
            return response()->json(['message' => 'Bu kullanıcı bir okula bağlı değil.'], 403);
        }

        // 📦 Aktif abonelik
        $subscription = $school->activeSubscription();

        if (!$subscription) {
            return response()->json(['message' => 'Bu okulun aktif bir aboneliği bulunmuyor.'], 403);
        }

        $package = $subscription->package;
        return $this->successResponse($package->gradeRules, 'Sınıf kuralları listelendi.');
    }
}
