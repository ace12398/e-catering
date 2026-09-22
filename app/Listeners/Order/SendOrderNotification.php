<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderStatusChanged;
use Illuminate\Support\Facades\Log;

class SendOrderNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        Log::info("Order #{$event->order->order_number} status updated from {$event->previousStatus} to {$event->newStatus}");
    }
}
