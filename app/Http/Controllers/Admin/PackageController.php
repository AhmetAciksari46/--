<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePackageRequest;
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
 *     name="Packages-Admin",
 *     description="Paket yönetimi ve satın alma işlemleri"
 * )
 */

/**
 * @OA\Tag(
 *     name="Packages-Manager",
 *     description="Paket yönetimi ve satın alma işlemleri"
 * )
 */
class PackageController extends Controller
{
    use ApiResponser;
    /**
     * @OA\Get(
     *     path="api/packages",
     *     tags={"Packages-Admin"},
     *     summary="Tüm paketleri listele",
     *     description="tüm paketleri listeler.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Paket listesi başarıyla getirildi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */


    public function getpackages()
    {

        $packages = Package::all();
        return $this->successResponse($packages);
    }

    /**
     * @OA\Get(
     *     path="api/packages/{id}",
     *     tags={"Packages-Admin"},
     *     summary="Paket ID ile paket görüntüle ",
     *     description="İdsi girilen paketi getirir.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Paket detayı başarıyla getirildi"),
     *     @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function getpackagebyid($id)
    {
        if (!auth()->user()->can('package.view')) {
            return $this->errorResponse('unauthorized', 403);
        }
        $this->authorizeRole(['admin']);
        return Package::with('subscriptions')->findOrFail($id);

        $package = Package::findOrFail($id);
        return $this->successResponse($package);
    }

    /**
     * @OA\Post(
     *     path="api/packages",
     *     tags={"Packages-Admin"},
     *     summary="Yeni Paket oluştur ",
     *     description="Paket Oluşturma işlemi.",
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

    public function create(CreatePackageRequest $request)
    {
        $package = Package::create($request->validated());
        return $this->successResponse($package->fresh(), 'Paket başarıyla oluşturuldu.');
    }
    //TODO: UpdatePackageRequest kullanılacak
    /**
     * @OA\Put(
     *     path="api/packages/{id}",
     *     tags={"Packages-Admin"},
     *     summary="Paket bilgilerini güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="name", type="string", example="Ede Koleji Güncel"),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Okul başarıyla güncellendi"),
     *     @OA\Response(response=500, description="Sunucu hatası")
     * )
     */
    public function update(UpdatePackageRequest $request, $id)
    {
        $package = Package::findOrFail($id);

        $package->update($request->validated());
        $message = "Paket başarıyla güncellendi.";
        return $this->successResponse($package->fresh(), $message);
    }
    /**
     * @OA\Delete(
     *     path="api/packages/{id}",
     *     tags={"Packages-Admin"},
     *     summary="Paket silme",
     *     description="ID ile paketi silme işlemi.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Silinecek paketin ID'si",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Paket başarıyla silindi"),
     *     @OA\Response(response=403, description="Yetkiniz yok"),
     *     @OA\Response(response=404, description="Paket bulunamadı")
     * )
     */
    public function delete($id)
    {
        if (!auth()->user()->can('package.delete')) {
            return $this->errorResponse('unauthorized', 403);
        }
        $package = Package::findOrFail($id);
        $package->delete();

        return $this->successResponse('Paket başarıyla silindi.');
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
