<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Package\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;
use App\Models\Subscription;
use App\Http\Requests\Package\PurchasePackageRequest;

/**
 * @OA\Tag(
 * name="Admin Packages",
 * description="Admin yetkisine sahip kullanıcılar için Paket (Abonelik Şablonu) yönetimi"
 * )
 */

class PackageController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     * path="/api/admin/packages",
     * summary="Paket Listesi",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\Response(
     * response=200,
     * description="Paketler başarıyla listelendi.",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Package")),
     * @OA\Property(property="links", type="object"),
     * @OA\Property(property="meta", type="object")
     * )
     * ),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function index()
    {
        // Tüm paketleri paginated olarak çekiyoruz.
        $packages = Package::paginate(15);
        return $this->successResponse($packages);
    }
    /**
     * @OA\Post(
     * path="/api/admin/packages",
     * summary="Yeni Paket Oluşturma",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/CreatePackageRequest")
     * ),
     * @OA\Response(
     * response=201,
     * description="Paket başarıyla oluşturuldu.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Paket başarıyla oluşturuldu."),
     * @OA\Property(property="package", ref="#/components/schemas/Package")
     * )
     * ),
     * @OA\Response(response=403, description="Yetkisiz Erişim"),
     * @OA\Response(response=422, description="Doğrulama Hatası")
     * )
     */
    public function store(CreatePackageRequest $request)
    {
        // StorePackageRequest, yetkilendirme ve doğrulamayı halletti.
        $package = Package::create($request->validated());
        return $this->successResponse($package->fresh(), 'Paket başarıyla oluşturuldu.', 201);
    }
    /**
     * @OA\Get(
     * path="/api/admin/packages/{package}",
     * summary="Paket Detayı",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Response(
     * response=200,
     * description="Paket detayı başarıyla getirildi.",
     * @OA\JsonContent(ref="#/components/schemas/Package")
     * ),
     * @OA\Response(response=404, description="Paket bulunamadı."),
     * @OA\Response(response=403, description="Yetkisiz Erişim")
     * )
     */
    public function show(Package $package)
    {
        // Paketin kurallarını da eager load ederek tam bir detay sunuyoruz.
        $package->load(['gradeRules', 'subjectRules']);
        return $this->successResponse($package);
    }

    /**
     * @OA\Put(
     * path="/api/admin/packages/{package}",
     * summary="Paket Güncelleme",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/UpdatePackageRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Paket başarıyla güncellendi.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Paket başarıyla güncellendi."),
     * @OA\Property(property="package", ref="#/components/schemas/Package")
     * )
     * ),
     * @OA\Response(response=404, description="Paket bulunamadı."),
     * @OA\Response(response=403, description="Yetkisiz Erişim"),
     * @OA\Response(response=422, description="Doğrulama Hatası")
     * )
     * @OA\Patch(
     * path="/api/admin/packages/{package}",
     * summary="Paket Kısmi Güncelleme (PATCH)",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/UpdatePackageRequest")
     * ),
     * @OA\Response(response=200, description="Paket başarıyla güncellendi.")
     * )
     */
    public function update(UpdatePackageRequest $request, Package $package)
    {
        $package->update($request->validated());
        return $this->successResponse($package->fresh(), 'Paket başarıyla güncellendi.');
    }

    /**
     * @OA\Delete(
     * path="/api/admin/packages/{package}",
     * summary="Paket Silme",
     * tags={"Admin Packages"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(name="package", in="path", required=true, @OA\Schema(type="integer"), description="Paket ID"),
     * @OA\Response(
     * response=200,
     * description="Paket başarıyla silindi.",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Paket ve ilişkili kuralları başarıyla silindi."))
     * ),
     * @OA\Response(response=404, description="Paket bulunamadı."),
     * @OA\Response(response=403, description="Yetkisiz Erişim"),
     * @OA\Response(response=409, description="Çakışma (Aktif abonelikler mevcut)")
     * )
     */
    public function destroy(Package $package)
    {
        // Kural: Aktif abonelikleri olan paket silinemez.
        if ($package->subscriptions()->exists()) {
            return $this->errorResponse('Paketin aktif veya geçmiş abonelikleri olduğu için silinemez. Lütfen sadece pasif yapın.', 409);
        }

        // İlişkili kuralları siliyoruz (Veritabanında CASCADE yoksa gereklidir)
        $package->gradeRules()->delete();
        $package->subjectRules()->delete();

        $package->delete();
        return $this->successResponse(null, 'Paket ve ilişkili kuralları başarıyla silindi.');
    }


    //*********************-Admin Kısmı bitti-*************************** */

    //TODO: V2.0 da eklenecek

    // Sadece aktif ve görünür olan paketleri getir
    // public function publicIndex()
    // {
    //     try {
    //         $packages = Package::where('is_active', true)
    //             ->where('is_visible', true)
    //             ->orderBy('sort_order')
    //             ->get();
    //         return $this->successResponse($packages, 'Paket listesi başarıyla getirildi.');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse('Paketler getirilirken bir hata oluştu: ' . $e->getMessage(), 500);
    //     }
    // }
    // Sadece aktif ve görünür paketi id ile gösterebilir

    //TODO: V2.0 da eklenecek

    /**
     * @OA\Get(
     *     path="/api/publicpackage/{id}",
     *     summary="İlgili Paketi Getirir",
     *     description="Paket detaylarını getirir.",
     *     tags={"Packages"},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Package"))
     *     )
     * )
     */

    // public function publicShow($id)
    // {
    //     $package = Package::where('is_active', true)
    //         ->where('is_visible', true)
    //         ->findOrFail($id);

    //     return response()->json([
    //         'message' => 'Paket detayları getirildi.',
    //         'data' => $package
    //     ]);
    //     return $this->successResponse($package, 'Paket detayları getirildi.');
    // }









    //TODO: PACKAGE SATIN ALMA İŞLEMLERİ - V2.0 DA EKLENECEK
    /**
     * @OA\Post(
     *     path="/manager/packages/{package}/purchase",
     *     tags={"Packages-Manager"},
     *     summary="Yeni Paket Satın al ",
     *     description="Paket Satın alma işlemi.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "duration_days","price", "type"},
     *             @OA\Property(property="name", type="string", example="Kış 40 Hafta Paketi"),
     *             @OA\Property(property="duration_days", type="integer", example=365),
     *             @OA\Property(property="price", type="integer", example=499.99),
     *             @OA\Property(property="type", type="string", example="school"),
     *             @OA\Property(property="description", type="string", example="yoğun içerikli paket"),
     *             @OA\Property(property="is_active", type="boolen", example=true),
     *             @OA\Property(property="is_visible", type="boolen", example=true),
     *             @OA\Property(property="is_trial", type="boolen", example=false),
     *             @OA\Property(property="has_homework_module", type="boolen", example=true),
     *             @OA\Property(property="has_exam_module", type="boolen", example=true),
     *             @OA\Property(property="has_chat_module", type="boolen", example=true),
     *             @OA\Property(property="has_analytics_module", type="boolen", example=true),
     *             @OA\Property(property="has_certificate_module", type="boolen", example=true),
     *             @OA\Property(property="trial_days", type="integer", example=15),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="img_path", type="string", example="uploads/packages/package1.png")    
     *         )
     *     ),
     *     @OA\Response(response=200, description="Paket başarıyla oluşturuldu"),
     *     @OA\Response(response=403, description="yetki hatası"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */
    // public function purchase(PurchasePackageRequest $request, Package $package)
    // {
    //     try {
    //         $user = $request->user();
    //         $subscription = Subscription::upgradeOrCreate($user, $package);
    //         $subscription->update(['payment_status' => 'paid']);
    //         //TODO: Ödeme entegrasyonu eklenecek
    //         // $data = [
    //         //     'subscription_id' => $subscription->id,
    //         //     'current_package' => [
    //         //         'id' => $subscription->package->id,
    //         //         'name' => $subscription->package->name,
    //         //         'description' => $subscription->package->description,
    //         //         'price' => $subscription->package->price,
    //         //         'duration_days' => $subscription->package->duration_days,
    //         //         'modules' => [
    //         //             'homework' => (bool) $subscription->package->has_homework_module,
    //         //             'exam' => (bool) $subscription->package->has_exam_module,
    //         //             'chat' => (bool) $subscription->package->has_chat_module,
    //         //             'analytics' => (bool) $subscription->package->has_analytics_module,
    //         //             'certificate' => (bool) $subscription->package->has_certificate_module,
    //         //         ],
    //         //     ],
    //         // ];

    //         return $this->successResponse($subscription, 'Ödeme başlatıldı, onay bekleniyor.');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse('Paket satın alınırken bir hata oluştu: ' . $e->getMessage(), 500);
    //     }
    // }

    // Admin manuel onay
    public function approvePayment(Request $request, Subscription $subscription)
    {
        if ($subscription->payment_status === 'paid') {
            return $this->successResponse(null, 'Ödeme zaten onaylanmış.');
        }

        // Ödemeyi onayla
        $subscription->update([
            'payment_status' => 'paid',
        ]);

        // Eğer subscribable manager ise
        $user = $subscription->subscribable;

        if ($user->role === 'manager') {
            // Manager profile al
            $profile = $user->managerProfile;

            if ($profile) {
                // Payment reminder aktif et
                $profile->update([
                    'payment_reminder' => true,
                ]);

                // School varsa ve paket aktifse school.is_active = true
                if ($profile->schoolId) {
                    $school = $profile->school; // Eğer School modeli relation varsa
                    if ($school) {
                        $school->update([
                            'is_active' => $subscription->package->is_active,
                        ]);
                    }
                }
            }
        }

        $data = [
            'subscription_id' => $subscription->id,
            'current_package' => $subscription->package,
            'payment_status' => $subscription->payment_status,
            'manager_profile' => $user->managerProfile ?? null,
        ];

        return $this->successResponse($data, 'Paket onaylandı ve manager profili güncellendi.');
    }
}
