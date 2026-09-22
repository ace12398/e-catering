<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AdaptiveEngine
{
    public function getContext(User $user): array
    {
        return Cache::remember("adaptive_ctx_{$user->id}", 300, function () use ($user) {
            $user->loadMissing(['profile', 'workspacePreference', 'orders']);
            $activityLogs = ActivityLog::where('user_id', $user->id);
            $orderCount = $user->orders()->count();
            $totalSpent = $user->orders()->sum('grand_total');
            $firstOrder = $user->orders()->oldest()->first();
            $pageVisits = $activityLogs->clone()->where('action', 'page_visit')
                ->select('entity_type')
                ->selectRaw('COUNT(*) as visit_count')
                ->groupBy('entity_type')
                ->orderByDesc('visit_count')
                ->limit(5)
                ->pluck('visit_count', 'entity_type')
                ->toArray();

            $frequentMenuIds = OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
                ->select('menu_id')
                ->selectRaw('SUM(quantity) as total_qty')
                ->groupBy('menu_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->pluck('total_qty', 'menu_id')
                ->toArray();

            $profileCompletion = $user->profile ? $user->profile->calculateCompletion() : 40;
            $isFirstLogin = $orderCount === 0 && $activityLogs->clone()->count() <= 1;
            $hasActivity = $activityLogs->clone()->count() > 0;
            $isOnboardingComplete = $user->workspacePreference?->onboarding_complete ?? false;
            $preset = $user->workspacePreference?->active_preset ?? 'institution_organization';
            $density = $user->workspacePreference?->density ?? 'comfortable';

            $hour = (int) now()->format('H');
            if ($hour < 12) { $greeting = 'Selamat Pagi'; $timeOfDay = 'morning'; }
            elseif ($hour < 17) { $greeting = 'Selamat Siang'; $timeOfDay = 'afternoon'; }
            else { $greeting = 'Selamat Malam'; $timeOfDay = 'evening'; }

            $weeksSinceFirst = $firstOrder ? $firstOrder->created_at->diffInWeeks(now()) + 1 : 1;
            $purchaseFrequency = round($orderCount / max($weeksSinceFirst, 1), 1);

            $roleLabel = match($user->role?->value ?? 'customer') {
                'admin', 'super_admin' => 'Administrator',
                default => 'Customer',
            };

            return [
                'role' => $user->role?->value ?? 'customer',
                'role_label' => $roleLabel,
                'is_admin' => $user->isAdmin(),
                'is_customer' => $user->isCustomer(),
                'profile_completion' => $profileCompletion,
                'is_first_login' => $isFirstLogin,
                'is_returning' => !$isFirstLogin && $hasActivity,
                'has_organization' => !empty($user->profile?->company_name),
                'onboarding_complete' => $isOnboardingComplete,
                'order_count' => $orderCount,
                'total_spent' => $totalSpent,
                'purchase_frequency' => $purchaseFrequency,
                'frequent_pages' => $pageVisits,
                'frequent_menu_ids' => array_keys($frequentMenuIds),
                'workspace_preset' => $preset,
                'density' => $density,
                'time_of_day' => $timeOfDay,
                'greeting' => $greeting,
                'company_name' => $user->profile?->company_name,
                'user_name' => $user->name,
                'avatar_url' => $user->profile?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
            ];
        });
    }

    public function getQuickActions(array $ctx): array
    {
        if ($ctx['is_admin']) {
            return [
                ['label' => 'Kelola Pesanan', 'url' => route('orders.index'), 'icon' => '📋', 'description' => 'Tinjau dan proses pesanan katering masuk'],
                ['label' => 'Antrean Dapur', 'url' => route('kitchen.index'), 'icon' => '👨‍🍳', 'description' => 'Pantau status persiapan pesanan di dapur'],
                ['label' => 'Pelacakan Pengiriman', 'url' => route('delivery.index'), 'icon' => '🛵', 'description' => 'Pantau status pengiriman oleh kurir'],
                ['label' => 'Ringkasan Keuangan', 'url' => route('finance.index'), 'icon' => '💳', 'description' => 'Lihat daftar tagihan dan pembayaran'],
            ];
        }

        return [
            ['label' => 'Pesan Menu Katering', 'url' => route('menus.index'), 'icon' => '🍜', 'description' => 'Lihat ragam pilihan paket katering'],
            ['label' => 'Keranjang Belanja', 'url' => route('cart.index'), 'icon' => '🛒', 'description' => 'Tinjau item yang siap di-checkout'],
            ['label' => 'Riwayat & Pelacakan', 'url' => route('orders.index'), 'icon' => '📜', 'description' => 'Lihat status pesanan dan pelacakan'],
            ['label' => 'Pengaturan Tampilan', 'url' => route('workspace.customize'), 'icon' => '⚙️', 'description' => 'Sesuaikan tata letak halaman beranda'],
        ];
    }

    public function getRecommendations(array $ctx): array
    {
        $recs = [];
        if (!$ctx['onboarding_complete']) {
            $recs[] = ['type' => 'onboarding', 'severity' => 'info', 'title' => 'Selamat Datang di CaterFlow!', 'description' => 'Selesaikan panduan awal untuk menyesuaikan antarmuka sesuai kebutuhan Anda.', 'action_url' => route('onboarding.show'), 'action_label' => 'Mulai Pengaturan →'];
        }
        if ($ctx['profile_completion'] < 100) {
            $recs[] = ['type' => 'profile', 'severity' => 'warning', 'title' => 'Lengkapi Profil Anda', 'description' => 'Tambahkan data profil dan alamat pengiriman untuk mempercepat checkout.', 'action_url' => route('profile.edit'), 'action_label' => 'Perbarui Profil →'];
        }
        if ($ctx['is_returning'] && $ctx['order_count'] > 2 && $ctx['profile_completion'] >= 80) {
            $recs[] = ['type' => 'loyalty', 'severity' => 'success', 'title' => 'Pelanggan Setia', 'description' => "Anda telah melakukan {$ctx['order_count']} kali pemesanan. Terima kasih atas kepercayaan Anda!", 'action_url' => route('orders.index'), 'action_label' => 'Lihat Riwayat →'];
        }
        return $recs;
    }

    public function getSidebarSections(array $ctx): array
    {
        $showAdminOps = $ctx['is_admin'];

        return [
            'main' => [
                ['label' => 'Beranda', 'route' => 'dashboard', 'icon' => '📊', 'visible' => true],
                ['label' => 'Katalog Menu', 'route' => 'menus.index', 'icon' => '🍜', 'visible' => true],
                ['label' => 'Keranjang Belanja', 'route' => 'cart.index', 'icon' => '🛒', 'visible' => !$showAdminOps],
            ],
            'operations' => [
                ['label' => 'Manajemen Dapur', 'route' => 'kitchen.index', 'icon' => '👨‍🍳', 'visible' => $showAdminOps],
                ['label' => 'Pengiriman & Kurir', 'route' => 'delivery.index', 'icon' => '🛵', 'visible' => $showAdminOps],
                ['label' => 'Keuangan & Tagihan', 'route' => 'finance.index', 'icon' => '💳', 'visible' => $showAdminOps],
                ['label' => 'Analisis Bisnis', 'route' => 'analytics.index', 'icon' => '📈', 'visible' => $showAdminOps],
            ],
            'orders' => [
                ['label' => $showAdminOps ? 'Semua Pesanan' : 'Riwayat & Pelacakan', 'route' => 'orders.index', 'icon' => '📦', 'visible' => true],
            ],
            'settings' => [
                ['label' => 'Profil Saya', 'route' => 'profile.edit', 'icon' => '👤', 'visible' => true],
                ['label' => 'Pengaturan Tampilan', 'route' => 'workspace.customize', 'icon' => '⚙️', 'visible' => true],
            ],
        ];
    }

    public function clearCache(int $userId): void
    {
        Cache::forget("adaptive_ctx_{$userId}");
    }
}
