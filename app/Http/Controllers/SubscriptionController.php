<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\Subscription;
use app\Models\Package;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\SubscriptionUpdateRequest;
use App\Http\Requests\PackageStoreSubscriptionRequest;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Eğer polymorphic subscribable tipi kullanıcı ise:
        $subscriptions = Subscription::where('subscribable_type', get_class($user))
            ->where('subscribable_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $subscriptions], 200);
    }
    // Admin için tüm abonelikleri getir (admin middleware ile koru)
    public function adminIndex()
    {
        $subscriptions = Subscription::with(['package', 'subscribable'])->orderByDesc('created_at')->get();
        return response()->json(['data' => $subscriptions], 200);
    }

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





    public function checkActive($schoolId)
    {
        $subscription = Subscription::where('school_id', $schoolId)
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
