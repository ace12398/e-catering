<?php
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'track.activity'])->group(function () {
    // Onboarding (ISO 9241-210 Stage 1)
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Adaptive Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Menu & Ordering (All authenticated users)
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{id}/upload-proof', [OrderController::class, 'uploadProof'])->name('orders.upload-proof');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Profile & Workspace (All authenticated users)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('/profile/delete-avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.customize');
    Route::post('/workspace/preset', [WorkspaceController::class, 'updatePreset'])->name('workspace.preset');
    Route::post('/workspace/save-layout', [WorkspaceController::class, 'saveLayout'])->name('workspace.save-layout');
    Route::post('/workspace/reset-layout', [WorkspaceController::class, 'resetLayout'])->name('workspace.reset-layout');

    // UX Feedback (ISO 9241-210 Stage 4)
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Admin-Only: Operasional & Manajemen
    Route::middleware(['role:admin,super_admin'])->group(function () {
        // Dapur
        Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::patch('/kitchen/{id}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.update-status');

        // Pengiriman & Kurir
        Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
        Route::patch('/delivery/{id}/status', [DeliveryController::class, 'updateStatus'])->name('delivery.update-status');

        // Keuangan & Tagihan
        Route::get('/finance', [InvoiceController::class, 'index'])->name('finance.index');
        Route::get('/finance/{id}', [InvoiceController::class, 'show'])->name('finance.show');
        Route::patch('/finance/{id}/verify', [InvoiceController::class, 'verify'])->name('finance.verify');
        Route::patch('/finance/{id}/reject', [InvoiceController::class, 'reject'])->name('finance.reject');

        // Analisis Bisnis
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

require __DIR__.'/auth.php';
