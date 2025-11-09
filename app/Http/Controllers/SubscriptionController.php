<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\Subscription;
use app\Models\Package;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\SubscriptionUpdateRequest;
use App\Http\Requests\PackageStoreSubscriptionRequest;

/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="Subscription işlemleri(Manager, Teacher, Student, Admin)",
 * )
 * @OAS\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="http",
 *      scheme="bearer"
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
    /**
     * @OA\Get(
     *     path="/api/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Kullanıcının tüm aboneliklerini listele",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Başarılı"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = Subscription::where('subscribable_type', get_class($user))
            ->where('subscribable_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['data' => $subscriptions], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Admin: Tüm abonelikleri listele",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Başarılı"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */


    // Admin için tüm abonelikleri getir (admin middleware ile koru)
    public function adminIndex()
    {

        $subscriptions = Subscription::with(['package', 'subscribable'])->orderByDesc('created_at')->get();
        return response()->json(['data' => $subscriptions], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Yeni abonelik oluştur",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PackageStoreSubscriptionRequest")
     *     ),
     *     @OA\Response(response=201, description="Abonelik oluşturuldu"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */

    // Yeni abonelik oluştur
    public function store(PackageStoreSubscriptionRequest $request)
    {
        $data = $request->validated();

        // Eğer frontend price göndermemişse paketten al
        $package = Package::findOrFail($data['package_id']);

        $price = $data['price'] ?? $package->price;
        $currency = $data['currency'] ?? 'TRY';

        // start / end date: yoksa paketin duration_days'ine göre ayarla
        if (empty($data['start_date'])) {
            $start = now();
        } else {
            $start = \Carbon\Carbon::parse($data['start_date']);
        }

        if (empty($data['end_date'])) {
            $end = (clone $start)->addDays($package->duration_days);
        } else {
            $end = \Carbon\Carbon::parse($data['end_date']);
        }

        // Transaction içinde oluştur
        $subscription = DB::transaction(function () use ($data, $price, $currency, $start, $end) {
            return Subscription::create([
                'subscribable_type' => $data['subscribable_type'],
                'subscribable_id' => $data['subscribable_id'],
                'package_id' => $data['package_id'],
                'price' => $price,
                'currency' => $currency,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'start_date' => $start,
                'end_date' => $end,
                'status' => ($data['payment_status'] ?? 'pending') === 'paid' ? 'active' : 'active', // ödeme sonrası mantık değişir
                'auto_renew' => $data['auto_renew'] ?? false,
                'note' => $data['note'] ?? null,
                'is_active' => true,
            ]);
        });

        return response()->json(['message' => 'Subscription created', 'data' => $subscription], 201);
    }


    /**
     * @OA\Get(
     *     path="/api/admin/subscriptions/{id}",
     *     tags={"Subscriptions"},
     *     summary="Abonelik detayını göster",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Abonelik ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Başarılı"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */

    public function show($id, Request $request)
    {
        $subscription = Subscription::with('package', 'subscribable')->findOrFail($id);

        // Eğer kullanıcı kendi aboneliğini görmek istiyorsa yetki kontrolü
        if ($request->user() && $subscription->subscribable_type === get_class($request->user()) && $subscription->subscribable_id === $request->user()->id) {
            return response()->json(['data' => $subscription], 200);
        }

        // Admin kontrolü
        if ($request->user() && $request->user()->can('viewAny', Subscription::class)) {
            return response()->json(['data' => $subscription], 200);
        }

        // Misafir/başka kullanıcı erişemez
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    /**
     * @OA\Put(
     *     path="/api/subscriptions/{id}",
     *     tags={"Subscriptions"},
     *     summary="Abonelik güncelle",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Abonelik ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionUpdateRequest")
     *     ),
     *     @OA\Response(response=200, description="Abonelik güncellendi"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    // Güncelle (admin veya owner)
    public function update(SubscriptionUpdateRequest $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        // Yetki kontrolü: admin veya sahibi olmalı
        $user = $request->user();
        $ownerClass = $subscription->subscribable_type;
        $ownerId = $subscription->subscribable_id;

        if (!($user && ($user->can('update', $subscription) || (get_class($user) === $ownerClass && $user->id == $ownerId)))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subscription->fill($request->validated());
        $subscription->save();

        return response()->json(['message' => 'Subscription updated', 'data' => $subscription], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions/{id}/cancel",
     *     tags={"Subscriptions"},
     *     summary="Aboneliği iptal et",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Abonelik ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Abonelik iptal edildi"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */

    // Cancel (abonelik iptali)
    public function cancel(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        // Yetki: sahibi veya admin
        $user = $request->user();
        $ownerClass = $subscription->subscribable_type;
        $ownerId = $subscription->subscribable_id;

        if (!($user && ($user->can('update', $subscription) || (get_class($user) === $ownerClass && $user->id == $ownerId)))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subscription->status = 'cancelled';
        $subscription->auto_renew = false;
        $subscription->is_active = false;
        $subscription->save();

        return response()->json(['message' => 'Subscription cancelled', 'data' => $subscription], 200);
    }

    /**
     * @OA\Post(
     *     path="/subscriptions/webhook/payment",
     *     tags={"Subscriptions"},
     *     summary="Ödeme webhook bildirimi al",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object", example={"payment_reference":"abc123", "payment_status":"paid"})
     *     ),
     *     @OA\Response(response=200, description="Webhook işlendi"),
     *     @OA\Response(response=404, description="Abonelik bulunamadı")
     * )
     */

    // Webhook örneği: ödeme sağlayıcısından gelen bildirimleri işler
    // /api/subscriptions/webhook/payment
    public function paymentWebhook(Request $request)
    {
        // Bu method uygulamaya göre değişecek; örnek:
        $payload = $request->all();
        // validate signature, provider, vs.

        // Örnek: payment_reference ile subscription bul
        if (!empty($payload['payment_reference'])) {
            $sub = Subscription::where('payment_reference', $payload['payment_reference'])->first();
            if ($sub) {
                $sub->payment_status = $payload['payment_status'] ?? $sub->payment_status;
                if (($payload['payment_status'] ?? null) === 'paid') {
                    $sub->status = 'active';
                    $sub->is_active = true;
                }
                $sub->save();
                return response()->json(['message' => 'updated'], 200);
            }
        }

        return response()->json(['message' => 'not found'], 404);
    }


    /**
     * @OA\Get(
     *     path="/subscriptions/check-active/{school_id}",
     *     tags={"Subscriptions"},
     *     summary="Okul aboneliğini kontrol et",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="school_id",
     *         in="path",
     *         required=true,
     *         description="Okul ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Abonelik aktif"),
     *     @OA\Response(response=403, description="Abonelik pasif veya yok")
     * )
     */


    public function checkActive($school_id)
    {
        $subscription = Subscription::where('school_id', $school_id)
            ->with('package')
            ->latest('end_date')
            ->first();

        if (!$subscription || !$subscription->isActive()) {
            return response()->json(['status' => 'inactive'], 403);
        }

        return response()->json([
            'status' => 'active',
            'package' => $subscription->package->name,
            'expires_at' => $subscription->end_date,
        ]);
    }
}
