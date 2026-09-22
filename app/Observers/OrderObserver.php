<?php

namespace App\Observers;

use App\Events\Order\OrderCreated;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (empty($order->order_number)) {
            $order->order_number = 'ORD-' . strtoupper(Str::random(8));
        }
    }

    public function created(Order $order): void
    {
        event(new OrderCreated($order));

        ActivityLog::create([
            'user_id' => $order->user_id,
            'action' => 'order_created',
            'entity_type' => Order::class,
            'entity_id' => $order->id,
            'new_values' => ['order_number' => $order->order_number, 'grand_total' => $order->grand_total],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
