<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user) {
            $preference = \App\Models\WorkspacePreference::firstOrCreate(
                ['user_id' => $user->id],
                ['layout_density' => 'comfortable']
            );
            $effectiveMatrix = $preference->effective_layout_matrix;
        } else {
            $effectiveMatrix = \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        }
        $cartSectionsOrder = $effectiveMatrix['cart_sections_order'] ?? ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'];

        $data = $this->cartService->getCartData($user);
        $data['layout_matrix'] = $effectiveMatrix;
        $data['cartSectionsOrder'] = $cartSectionsOrder;

        return view('cart.index', $data);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_id'  => 'required|exists:menus,id',
            'quantity' => 'nullable|integer|min:1',
            'notes'    => 'nullable|string|max:255',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $this->cartService->addToCart(
            user: $request->user(),
            menuId: (int) $validated['menu_id'],
            quantity: $quantity,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('cart.index')->with('success', 'Menu berhasil ditambahkan ke keranjang belanja!');
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cartService->updateQuantity(
            user: $request->user(),
            cartItemId: $id,
            quantity: (int) $validated['quantity']
        );

        return redirect()->route('cart.index')->with('success', 'Jumlah pesanan berhasil diperbarui!');
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $this->cartService->removeFromCart($request->user(), $id);

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang!');
    }
}
