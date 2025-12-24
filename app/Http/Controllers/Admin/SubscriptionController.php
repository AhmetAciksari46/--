<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Package;
use App\Traits\ApiResponser;

use Illuminate\Support\Facades\DB;

use App\Http\Requests\SubscriptionUpdateRequest;
use App\Http\Requests\PackageStoreSubscriptionRequest;

/**
 * @OA\Tag(
 *     name="Admin: Subscriptions",
 *     description="Admin tarafından abonelik yönetimi"
 * )
 */


/**
 * @OA\Schema(
 *     schema="SubscriptionUpdateRequest",
 *     type="object",
 *     title="Subscription Güncelleme Request",
 *     required={},
 *     @OA\Property(property="package_id", type="integer", example=2, nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=199.99, nullable=true),
 *     @OA\Property(property="currency", type="string", example="TRY", nullable=true),
 *     @OA\Property(property="payment_method", type="string", example="credit_card", nullable=true),
 *     @OA\Property(property="payment_reference", type="string", example="TX123456", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2025-10-21T12:00:00Z", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2025-11-21T12:00:00Z", nullable=true),
 *     @OA\Property(property="auto_renew", type="boolean", example=false, nullable=true),
 *     @OA\Property(property="note", type="string", example="Özel not", nullable=true)
 * )
 */



class SubscriptionController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/admin/subscriptions",
     *     tags={"Admin: Subscriptions"},
     *     summary="Tüm abonelikleri listele",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function index()
    {
        if (!auth()->user()->can('subscription.view.list')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscriptions = Subscription::latest()->get();
        return $this->successResponse($subscriptions, 'Abonelikler başarıyla getirildi', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/create",
     *     tags={"Admin: Subscriptions"},
     *     summary="Yeni abonelik oluştur (manuel)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subscribable_type","subscribable_id","package_id"},
     *             @OA\Property(property="subscribable_type", type="string", example="App\\Models\\School"),
     *             @OA\Property(property="subscribable_id", type="integer", example=1),
     *             @OA\Property(property="package_id", type="integer", example=2),
     *             @OA\Property(property="price", type="number", example=200),
     *             @OA\Property(property="payment_status", type="string", example="paid"),
     *             @OA\Property(property="start_date", type="string", example="2025-01-01 00:00:00"),
     *             @OA\Property(property="end_date", type="string", example="2025-12-31 23:59:59"),
     *             @OA\Property(property="auto_renew", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Abonelik oluşturuldu")
     * )
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $validated = $request->validate([
            'subscribable_type' => 'required|string',
            'subscribable_id'   => 'required|integer',
            'package_id'        => 'required|exists:packages,id',
            'price'             => 'required|numeric',
            'payment_status'    => 'nullable|string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'auto_renew'        => 'boolean',
        ]);

        $subscription = Subscription::create($validated);
        return $this->successResponse($subscription, 'Abonelik başarıyla oluşturuldu.', 200);
    }



    /**
     * @OA\Get(
     *     path="/api/admin/subscriptions/{id}",
     *     tags={"Admin: Subscriptions"},
     *     summary="Abonelik detaylarını görüntüle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Başarılı")
     * )
     */
    public function show(Subscription $subscription)
    {
        if (!auth()->user()->can('subscription.view')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        return $this->successResponse($subscription, 'Abonelik getirildi.', 200);
    }
    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/unlimited",
     *     tags={"Admin: Subscriptions"},
     *     summary="Sadece school_id ve package_id ile süresiz abonelik başlat",
     *     security={{"bearerAuth":{}}},
     *     description="subscribable_type otomatik olarak App\\Models\\School olarak atanır.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"school_id","package_id"},
     *             @OA\Property(property="school_id", type="integer", example=10),
     *             @OA\Property(property="package_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Süresiz abonelik oluşturuldu")
     * )
     */
    public function createUnlimited(Request $request)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $validated = $request->validate([
            'school_id'  => 'required|exists:schools,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        $subscription = Subscription::create([
            'subscribable_type' => 'App\\Models\\School',
            'subscribable_id'   => $validated['school_id'],
            'package_id'        => $validated['package_id'],
            'price'             => 0,
            'currency'          => 'TRY',
            'payment_status'    => 'paid',
            'status'            => 'active',
            'start_date'        => now(),
            'end_date'          => null, // SÜRESİZ
            'is_active'         => true,
            'auto_renew'        => true,
            'note'              => 'Süresiz abonelik (manuel) oluşturuldu'
        ]);

        return response()->json([
            'message' => 'Süresiz abonelik başarıyla oluşturuldu',
            'subscription' => $subscription
        ], 201);
    }




    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/upgrade",
     *     tags={"Admin: Subscriptions"},
     *     summary="Mevcut aboneliğin paketini yükselt (upgrade)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"package_id"},
     *             @OA\Property(property="package_id", type="integer", example=3),
     *             @OA\Property(property="price", type="number", example=299.90)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Yükseltme başarılı")
     * )
     */
    public function upgrade(Request $request, $id)
    {
        if (!auth()->user()->can('subscription.update')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);
        $package = Package::findOrFail($request->package_id);

        $subscription->update(['is_active' => false]);

        $new = Subscription::create([
            'subscribable_type' => $subscription->subscribable_type,
            'subscribable_id'   => $subscription->subscribable_id,
            'package_id'        => $package->id,
            'price'             => $request->price ?? $package->price,
            'currency'          => 'TRY',
            'payment_status'    => 'pending',
            'status'            => 'active',
            'start_date'        => now(),
            'end_date'          => now()->addDays($package->duration_days),
            'auto_renew'        => false,
        ]);
        return $this->successResponse($new, 'Paket başarıyla yükseltildi', 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/renew",
     *     tags={"Admin: Subscriptions"},
     *     summary="Abonelik süresini uzat (manuel yenileme)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"end_date"},
     *             @OA\Property(property="end_date", type="string", example="2026-01-01 00:00:00")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Abonelik yenilendi")
     * )
     */
    public function renew(Request $request, $id)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);

        $subscription->update([
            'end_date' => $request->end_date,
            'status' => 'active',
            'is_active' => true,
        ]);
        return $this->successResponse($subscription, 'Abonelik yenilendi', 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/cancel",
     *     tags={"Admin: Subscriptions"},
     *     summary="Aboneliği iptal et",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Ödeme yapılmadı")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Abonelik iptal edildi")
     * )
     */
    public function cancel(Request $request, $id)
    {
        if (!auth()->user()->can('subscription.delete')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);

        $subscription->update([
            'status' => 'cancelled',
            'is_active' => false,
            'note' => $request->reason ?? null,
        ]);
        return $this->successResponse($subscription, 'Abonelik iptal edildi', 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/payment",
     *     tags={"Admin: Subscriptions"},
     *     summary="Ödeme durumunu güncelle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_status", type="string", example="paid"),
     *             @OA\Property(property="payment_method", type="string", example="credit_card"),
     *             @OA\Property(property="payment_reference", type="string", example="TX123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Ödeme güncellendi")
     * )
     */
    public function updatePayment(Request $request, $id)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);

        $subscription->update($request->only([
            'payment_status',
            'payment_method',
            'payment_reference'
        ]));
        return $this->successResponse($subscription, 'Ödeme bilgisi güncellendi', 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/deactivate",
     *     tags={"Admin: Subscriptions"},
     *     summary="Aboneliği pasif yap",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Abonelik pasif edildi")
     * )
     */
    public function deactivate($id)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['is_active' => false]);
        return $this->successResponse($subscription, 'Abonelik pasif edildi', 200);
    }



    /**
     * @OA\Post(
     *     path="/api/admin/subscriptions/{id}/activate",
     *     tags={"Admin: Subscriptions"},
     *     summary="Aboneliği aktif yap",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Abonelik aktifleştirildi")
     * )
     */
    public function activate($id)
    {
        if (!auth()->user()->can('subscription.create')) {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz yok.', 403);
        }
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['is_active' => true, 'status' => 'active']);
        return $this->successResponse($subscription, 'Abonelik aktifleştirildi', 200);
    }


    // /**
    //  * @OA\Get(
    //  *     path="/api/admin/subscription",
    //  *     tags={"Manager: Subscription"},
    //  *     summary="Manager kendi okulunun aboneliğini görüntüler",
    //  *     @OA\Response(response=200, description="Başarılı")
    //  * )
    //  */
    // public function mySubscription()
    // {
    //     $user = auth()->user();
    //     return response()->json($user->school->subscription);
    // }
}
