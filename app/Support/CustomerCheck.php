<?php

namespace App\Support;

use App\Exceptions\CourierException;
use App\Models\CourierCheck;
use App\Models\Store;
use App\Services\SteadfastClient;

class CustomerCheck
{
    /** The feature stays hidden until an address is configured (or fake mode is on). */
    public function enabled(): bool
    {
        return (bool) config('services.steadfast.fake') || filled(config('services.steadfast.fraud_path'));
    }

    public function check(Store $store, string $phone, bool $force = false): CourierCheck
    {
        if (! preg_match('/^01[3-9]\d{8}$/', $phone)) {
            throw new CourierException('This is not a valid mobile number.');
        }

        $existing = $store->courierChecks()->where('phone', $phone)->first();

        // Saved results are reused for 24 hours
        if ($existing && ! $force && $existing->isFresh()) {
            return $existing;
        }

        if (! $store->hasSteadfast() && ! config('services.steadfast.fake')) {
            throw new CourierException('Add your Steadfast API keys in Store settings first.');
        }

        $result = (new SteadfastClient($store))->fraudCheck($phone);

        return $store->courierChecks()->updateOrCreate(
            ['phone' => $phone],
            $result + ['checked_at' => now()]
        );
    }
}