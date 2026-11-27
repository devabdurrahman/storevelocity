<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\SteadfastClient;
use App\Support\ApplyCourierStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSteadfastStatuses extends Command
{
    protected $signature = 'steadfast:sync';
    protected $description = 'Ask Steadfast for the latest status of parcels that are still on their way';

    public function handle(ApplyCourierStatus $apply): int
    {
        $final = ['delivered', 'partial_delivered', 'cancelled'];

        $orders = Order::query()
            ->whereNotNull('consignment_id')
            ->where(fn ($query) => $query->whereNull('courier_status')->orWhereNotIn('courier_status', $final))
            ->where('courier_booked_at', '>=', now()->subDays(30))
            ->with('store')
            ->orderBy('id')
            ->limit(200)
            ->get();

        $updated = 0;

        foreach ($orders as $order) {
            $store = $order->store;

            if (! $store || (! $store->hasSteadfast() && ! config('services.steadfast.fake'))) {
                continue;
            }

            try {
                $status = (new SteadfastClient($store))->statusByConsignment($order->consignment_id);

                if ($status !== '' && $status !== $order->courier_status) {
                    $apply->handle($order, $status);
                    $updated++;
                }
            } catch (\Throwable $e) {
                // Never log keys. Leave the order for the next run.
                Log::warning('Steadfast status check failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Checked {$orders->count()} parcels, updated {$updated}.");

        return self::SUCCESS;
    }
}