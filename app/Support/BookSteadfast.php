<?php

namespace App\Support;

use App\Exceptions\CourierException;
use App\Models\Order;
use App\Services\SteadfastClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookSteadfast
{
    public function handle(Order $order): void
    {
        $store = $order->store;

        if (! $store->hasSteadfast() && ! config('services.steadfast.fake')) {
            throw new CourierException('Add your Steadfast API keys in Store settings first.');
        }

        DB::transaction(function () use ($order, $store) {
            // Lock this order so two clicks can never book two parcels
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->consignment_id) {
                throw new CourierException('This order was already sent to Steadfast.');
            }

            if ($locked->status !== 'confirmed') {
                throw new CourierException('Only confirmed orders can be sent to Steadfast.');
            }

            $result = (new SteadfastClient($store))->createOrder($locked);

            $locked->update([
                'consignment_id' => $result['consignment_id'],
                'tracking_code' => $result['tracking_code'],
                'courier_status' => $result['status'],
                'courier_booked_at' => now(),
                'status' => 'shipped',
            ]);
        });
    }

    /**
     * Sends several orders one after another and reports each result.
     *
     * @return array{booked: int, skipped: int, failed: array<int, string>}
     */
    public function handleMany(Collection $orders): array
    {
        $booked = 0;
        $skipped = 0;
        $failed = [];

        foreach ($orders as $order) {
            if ($order->status !== 'confirmed' || filled($order->consignment_id)) {
                $skipped++;

                continue;
            }

            try {
                $this->handle($order);
                $booked++;
            } catch (CourierException $e) {
                $failed[] = '#' . $order->number . ': ' . $e->getMessage();

                // Wrong keys would fail every remaining order too
                if (in_array($e->getMessage(), [SteadfastClient::AUTH_FAILED, 'Add your Steadfast API keys in Store settings first.'], true)) {
                    break;
                }
            } catch (ConnectionException) {
                $failed[] = '#' . $order->number . ': could not reach Steadfast';
            }
        }

        return compact('booked', 'skipped', 'failed');
    }
}