<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'public_id', 'number', 'customer_name', 'customer_phone',
        'customer_address', 'customer_note', 'payment_method', 'status',
        'subtotal', 'shipping_fee', 'total', 'consignment_id', 'tracking_code', 'courier_status', 'courier_message', 'courier_booked_at', 'return_requested_at', 'delivery_zone',
    ];

    protected function casts(): array
    {
        return [
            'courier_booked_at' => 'datetime',
            'return_requested_at' => 'datetime',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        // Cancelled is a final state: block moving a cancelled order back,
        // because that would need a fresh stock check.
        static::updating(function (Order $order) {
            if ($order->getOriginal('status') === 'cancelled' && $order->status !== 'cancelled') {
                return false;
            }
        });

        // When an order becomes cancelled, put its items back into stock.
        static::updated(function (Order $order) {
            if ($order->wasChanged('status')
                && $order->status === 'cancelled'
                && $order->getOriginal('status') !== 'cancelled') {

                DB::transaction(function () use ($order) {
                    foreach ($order->items as $item) {
                        if ($item->variant_id) {
                            \App\Models\ProductVariant::whereKey($item->variant_id)->increment('stock', $item->quantity);
                        } elseif ($item->product_id) {
                            \App\Models\Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                        }
                    }
                });
            }
        });
    }

    public function courierCheck(): ?CourierCheck
    {
        return CourierCheck::lookup($this->store_id, $this->customer_phone);
    }
}