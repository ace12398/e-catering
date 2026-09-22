<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\KitchenTask;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createFromCart(User $user, array $validated): Order
    {
        return DB::transaction(function () use ($user, $validated) {
            $cart = Cart::where('user_id', $user->id)->with('items.menu')->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new \InvalidArgumentException('Keranjang belanja Anda kosong.');
            }

            $subtotal = $cart->items->sum(function ($item) {
                $unitPrice = $item->unit_price ?? $item->menu?->price ?? 0;
                return $unitPrice * $item->quantity;
            });

            $tax = round($subtotal * 0.11);
            $deliveryFee = $subtotal > 0 ? 25000 : 0;
            $discount = (float) ($cart->discount_amount ?? 0);
            $grandTotal = max($subtotal + $tax + $deliveryFee - $discount, 0);

            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad((string) (Order::withTrashed()->max('id') + 1), 4, '0', STR_PAD_LEFT);

            $paymentMethodName = 'Transfer Bank';
            if (!empty($validated['payment_method_id'])) {
                $pm = PaymentMethod::find($validated['payment_method_id']);
                if ($pm) {
                    $paymentMethodName = $pm->name;
                }
            }

            // Status awal: MENUNGGU PEMBAYARAN
            $order = Order::create([
                'order_number'     => $orderNumber,
                'user_id'          => $user->id,
                'subtotal'         => $subtotal,
                'tax'              => $tax,
                'discount'         => $discount,
                'grand_total'      => $grandTotal,
                'status'           => OrderStatus::WAITING_PAYMENT,
                'payment_status'   => PaymentStatus::UNPAID,
                'payment_method'   => $paymentMethodName,
                'delivery_address' => $validated['delivery_address'],
                'notes'            => $validated['notes'] ?? null,
                'delivery_time'    => !empty($validated['delivery_date']) ? $validated['delivery_date'] : now()->addHours(24),
            ]);

            foreach ($cart->items as $cartItem) {
                $unitPrice = (float) ($cartItem->unit_price ?? $cartItem->menu?->price ?? 0);
                $itemSubtotal = $unitPrice * $cartItem->quantity;

                OrderItem::create([
                    'order_id'  => $order->id,
                    'menu_id'   => $cartItem->menu_id,
                    'item_name' => $cartItem->menu?->name ?? 'Menu Katering',
                    'quantity'  => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal'  => $itemSubtotal,
                    'notes'     => $cartItem->notes,
                ]);
            }

            // Kitchen Task (waiting - akan aktif setelah admin verifikasi)
            KitchenTask::create([
                'uuid'             => (string) Str::uuid(),
                'order_id'         => $order->id,
                'assigned_chef_id' => null,
                'station'          => 'Dapur Utama',
                'priority'         => 'normal',
                'status'           => 'waiting',
                'notes'            => $validated['notes'] ?? null,
            ]);

            // Delivery (waiting)
            Delivery::create([
                'uuid'            => (string) Str::uuid(),
                'order_id'        => $order->id,
                'courier_id'      => null,
                'vehicle'         => 'Motor',
                'status'          => 'waiting',
                'priority'        => 'normal',
                'recipient_name'  => $user->name,
                'recipient_phone' => $user->profile?->phone_number ?? '081234567890',
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Invoice status: BELUM DIBAYAR
            Invoice::create([
                'uuid'           => (string) Str::uuid(),
                'invoice_number' => 'INV-' . substr($orderNumber, 4),
                'order_id'       => $order->id,
                'customer_id'    => $user->id,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'grand_total'    => $grandTotal,
                'status'         => 'belum_dibayar',
                'due_date'       => now()->addDays(1),
            ]);

            // Clear cart
            $cart->items()->delete();

            return $order;
        });
    }
}
