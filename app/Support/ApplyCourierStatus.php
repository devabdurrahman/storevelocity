<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ApplyCourierStatus
{
    public function handle(Order $order, ?string $courierStatus, ?string $message = null): void
    {
        DB::transaction(function () use ($order, $courierStatus, $message) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (filled($message)) {
                $locked->courier_message = mb_substr($message, 0, 500);
            }

            if (filled($courierStatus)) {
                $locked->courier_status = $courierStatus;

                // Only the final courier states change our own order status.
                // The *_approval_pending states are not final yet.
                $newStatus = match ($courierStatus) {
                    'delivered', 'partial_delivered' => 'delivered',
                    'cancelled' => 'cancelled',
                    default => null,
                };

                // Never move an order out of a final state
                if ($newStatus && ! in_array($locked->status, ['delivered', 'cancelled'], true)) {
                    $locked->status = $newStatus;
                }
            }

            // A change to "cancelled" triggers the stock restore from Step 37
            $locked->save();
        });
    }
}