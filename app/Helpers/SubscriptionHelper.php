<?php

namespace App\Helpers;

use App\Models\Subscription;

class SubscriptionHelper
{
    /**
     * Süresi dolmuş tüm abonelikleri kontrol et ve güncelle
     */
    public static function expireSubscriptions()
    {
        $subscriptions = Subscription::where('payment_status', 'paid')
            ->where('is_active', true)
            ->where('end_date', '<', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            self::expireSingle($subscription);
        }
    }

    /**
     * Tek bir subscription için expire işlemi
     */
    public static function expireSingle(Subscription $subscription)
    {
        $subscription->update([
            'is_active' => false,
            'status' => 'expired',
        ]);

        $user = $subscription->subscribable;
        if ($user && $user->role === 'manager') {
            $profile = $user->managerProfile;
            if ($profile) {
                $profile->update([
                    'payment_reminder' => false,
                ]);

                if ($profile->school) {
                    $profile->school->update([
                        'is_active' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Ödeme onayı sonrası manager profile ve school güncelle
     */
    public static function handlePaymentApproved(Subscription $subscription)
    {
        $user = $subscription->subscribable;

        if ($user && $user->role === 'manager') {
            $profile = $user->managerProfile;
            if ($profile) {
                $profile->update([
                    'payment_reminder' => true,
                ]);

                if ($profile->school) {
                    $profile->school->update([
                        'is_active' => $subscription->package->is_active,
                    ]);
                }
            }
        }
    }
}
