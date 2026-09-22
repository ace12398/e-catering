<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getOrCreateForUser(int $userId): Cart
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(Cart $cart, int $menuId, int $quantity = 1, ?string $notes = null): Cart
    {
        return DB::transaction(function () use ($cart, $menuId, $quantity, $notes) {
            $menu = Menu::findOrFail($menuId);
            
            $item = $cart->items()->where('menu_id', $menuId)->first();
            if ($item) {
                $item->update([
                    'quantity' => $item->quantity + $quantity,
                    'unit_price' => (float) $menu->price,
                    'notes' => $notes ?? $item->notes,
                ]);
            } else {
                $cart->items()->create([
                    'menu_id' => $menuId,
                    'quantity' => $quantity,
                    'unit_price' => (float) $menu->price,
                    'notes' => $notes,
                ]);
            }

            return $cart->fresh('items.menu');
        });
    }

    public function removeItem(Cart $cart, int $cartItemId): Cart
    {
        return DB::transaction(function () use ($cart, $cartItemId) {
            $cart->items()->where('id', $cartItemId)->delete();
            return $cart->fresh('items.menu');
        });
    }
}
