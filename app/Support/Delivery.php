<?php

namespace App\Support;

use App\Exceptions\CheckoutException;
use App\Models\Store;

/**
 * Which delivery areas a store serves, and what each one costs.
 * Everything that shows or charges delivery goes through here.
 */
class Delivery
{
    public const INSIDE = 'inside_dhaka';
    public const OUTSIDE = 'outside_dhaka';

    /**
     * The areas the store has switched on.
     *
     * @return array<string, array{key: string, label: string, fee: float, free_over: ?float}>
     */
    public static function zones(Store $store): array
    {
        $zones = [];

        if ($store->delivery_inside_enabled) {
            $zones[self::INSIDE] = [
                'key' => self::INSIDE,
                'label' => 'Inside Dhaka',
                'fee' => (float) $store->shipping_fee,
                'free_over' => $store->delivery_inside_free_over !== null ? (float) $store->delivery_inside_free_over : null,
            ];
        }

        if ($store->delivery_outside_enabled && $store->delivery_outside_fee !== null) {
            $zones[self::OUTSIDE] = [
                'key' => self::OUTSIDE,
                'label' => 'Outside Dhaka',
                'fee' => (float) $store->delivery_outside_fee,
                'free_over' => $store->delivery_outside_free_over !== null ? (float) $store->delivery_outside_free_over : null,
            ];
        }

        // Safety net: a store can never end up with no way to deliver
        if (! $zones) {
            $zones[self::INSIDE] = ['key' => self::INSIDE, 'label' => 'Inside Dhaka', 'fee' => (float) $store->shipping_fee, 'free_over' => null];
        }

        return $zones;
    }

    /** The delivery charge for one area and a given order subtotal. */
    public static function feeFor(Store $store, string $zone, float $subtotal): float
    {
        $area = self::zones($store)[$zone] ?? null;

        if (! $area) {
            throw new CheckoutException('Please choose where we should deliver.');
        }

        if ($area['free_over'] !== null && $subtotal >= $area['free_over']) {
            return 0.0;
        }

        return $area['fee'];
    }

    public static function label(?string $zone): string
    {
        return match ($zone) {
            self::INSIDE => 'Inside Dhaka',
            self::OUTSIDE => 'Outside Dhaka',
            default => 'Not recorded',
        };
    }

    public static function money(float $amount): string
    {
        return '৳' . number_format($amount, fmod($amount, 1) ? 2 : 0);
    }

    /** For example: "Inside Dhaka ৳60, Outside Dhaka ৳120 (free over ৳2,000)". */
    public static function summary(Store $store): string
    {
        return collect(self::zones($store))
            ->map(function (array $zone) {
                $text = $zone['label'] . ' ' . self::money($zone['fee']);

                if ($zone['free_over'] !== null) {
                    $text .= ' (free over ' . self::money($zone['free_over']) . ')';
                }

                return $text;
            })
            ->implode(', ');
    }
}