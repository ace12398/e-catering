<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrCreateForUser(int $userId): Cart;

    public function addItem(Cart $cart, int $menuId, int $quantity = 1, ?string $notes = null): Cart;

    public function removeItem(Cart $cart, int $cartItemId): Cart;
}
