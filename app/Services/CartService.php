<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get cart details, calculated totals, and active payment methods for a user.
     */
    public function getCartData(User $user): array
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItems = CartItem::with('menu')->where('cart_id', $cart->id)->get();

        $subtotal = $cartItems->sum(fn ($item) => ($item->unit_price ?? $item->menu?->price ?? 0) * $item->quantity);
        $tax = round($subtotal * 0.11); // 11% PPN
        $deliveryFee = $subtotal > 0 ? 25000 : 0;
        $total = $subtotal + $tax + $deliveryFee - ($cart->discount_amount ?? 0);

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return [
            'cart'           => $cart,
            'cartItems'      => $cartItems,
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'deliveryFee'    => $deliveryFee,
            'total'          => max($total, 0),
            'paymentMethods' => $paymentMethods,
        ];
    }

    /**
     * Add a menu item to the user's cart inside a DB transaction.
     */
    public function addToCart(User $user, int $menuId, int $quantity = 1, ?string $notes = null): CartItem
    {
        return DB::transaction(function () use ($user, $menuId, $quantity, $notes) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);
            $menu = Menu::findOrFail($menuId);

            $unitPrice = (float) $menu->price;
            $quantity = max(1, $quantity);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('menu_id', $menu->id)
                ->first();

            if ($item) {
                $item->quantity += $quantity;
                $item->unit_price = $unitPrice;
                if ($notes !== null) {
                    $item->notes = $notes;
                }
                $item->save();
            } else {
                $item = CartItem::create([
                    'cart_id'    => $cart->id,
                    'menu_id'    => $menu->id,
                    'quantity'   => $quantity,
                    'unit_price' => $unitPrice,
                    'notes'      => $notes,
                ]);
            }

            return $item;
        });
    }

    /**
     * Update quantity of a cart item.
     */
    public function updateQuantity(User $user, int $cartItemId, int $quantity): bool
    {
        return DB::transaction(function () use ($user, $cartItemId, $quantity) {
            $cart = Cart::where('user_id', $user->id)->first();
            if (!$cart) {
                return false;
            }

            $item = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->first();
            if (!$item) {
                return false;
            }

            if ($quantity <= 0) {
                return $item->delete();
            }

            return $item->update(['quantity' => $quantity]);
        });
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart(User $user, int $cartItemId): bool
    {
        return DB::transaction(function () use ($user, $cartItemId) {
            $cart = Cart::where('user_id', $user->id)->first();
            if (!$cart) {
                return false;
            }

            return CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->delete() > 0;
        });
    }

    /**
     * Clear all items in the user's cart.
     */
    public function clearCart(User $user): void
    {
        DB::transaction(function () use ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
            }
        });
    }
}
