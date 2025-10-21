<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;
use App\Models\Subscription;
use App\Http\Requests\Package\PurchasePackageRequest;

class PackageController extends Controller
{
    use ApiResponser;
    public function getpackages()
    {

        $packages = Package::all();
        return $this->successResponse($packages);
    }

    // Sadece aktif ve görünür olan paketleri getir
    public function publicIndex()
    {
        try {
            $packages = Package::where('is_active', true)
                ->where('is_visible', true)
                ->orderBy('sort_order')
                ->get();
            return $this->successResponse($packages, 'Paket listesi başarıyla getirildi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Paketler getirilirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }
    // Sadece aktif ve görünür paketi id ile gösterebilir


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

    public function publicShow($id)
    {
        $package = Package::where('is_active', true)
            ->where('is_visible', true)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Paket detayları getirildi.',
            'data' => $package
        ]);
        return $this->successResponse($package, 'Paket detayları getirildi.');
    }



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

    public function create(CreatePackageRequest $request)
    {
        $package = Package::create($request->validated());
        return $this->successResponse($package->fresh(), 'Paket başarıyla oluşturuldu.');
    }
    public function update(UpdatePackageRequest $request, $id)
    {
        $package = Package::findOrFail($id);

        $package->update($request->validated());
        $message = "Paket başarıyla güncellendi.";
        return $this->successResponse($package->fresh(), $message);
    }
    public function delete($id)
    {
        if (!auth()->user()->can('package.delete')) {
            return $this->errorResponse('unauthorized', 403);
        }
        $package = Package::findOrFail($id);
        $package->delete();

        return $this->successResponse('Paket başarıyla silindi.');
    }

    public function purchase(PurchasePackageRequest $request, Package $package)
    {
        try {
            $user = $request->user();
            $subscription = Subscription::upgradeOrCreate($user, $package);


            $data = [
                'subscription_id' => $subscription->id,
                'current_package' => [
                    'id' => $subscription->package->id,
                    'name' => $subscription->package->name,
                    'description' => $subscription->package->description,
                    'price' => $subscription->package->price,
                    'duration_days' => $subscription->package->duration_days,
                    'modules' => [
                        'homework' => (bool) $subscription->package->has_homework_module,
                        'exam' => (bool) $subscription->package->has_exam_module,
                        'chat' => (bool) $subscription->package->has_chat_module,
                        'analytics' => (bool) $subscription->package->has_analytics_module,
                        'certificate' => (bool) $subscription->package->has_certificate_module,
                    ],
                ],
            ];

            return $this->successResponse($data, 'Ödeme başlatıldı, onay bekleniyor.');
        } catch (\Exception $e) {
            return $this->errorResponse('Paket satın alınırken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

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
