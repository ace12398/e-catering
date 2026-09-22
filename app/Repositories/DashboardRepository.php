<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\KitchenTask;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardRepository
{
    /**
     * Get analytics data for a specific user (Customer view).
     */
    public function getAnalytics(int $userId): array
    {
        return [
            'total_orders'     => Order::where('user_id', $userId)->count(),
            'pending_orders'   => Order::where('user_id', $userId)->where('status', 'pending')->count(),
            'preparing_orders' => Order::where('user_id', $userId)->where('status', 'preparing')->count(),
            'completed_orders' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
            'total_spent'      => Order::where('user_id', $userId)->where('payment_status', 'paid')->sum('grand_total') ?? 0,
        ];
    }

    /**
     * Get analytics data for Admin (global view).
     */
    public function getAdminAnalytics(): array
    {
        return Cache::remember('admin_dashboard_analytics', 120, function () {
            $today = now()->startOfDay();
            return [
                'total_orders'          => Order::count(),
                'today_orders'          => Order::where('created_at', '>=', $today)->count(),
                'preparing_orders'      => Order::where('status', 'preparing')->count(),
                'completed_orders'      => Order::where('status', 'completed')->count(),
                'total_customers'       => User::count(),
                'total_revenue'         => Invoice::where('status', 'paid')->sum('grand_total') ?? 0,
                'active_kitchen_tasks'  => KitchenTask::whereIn('status', ['waiting', 'cooking', 'packaging'])->count(),
                'active_deliveries'     => Delivery::whereIn('status', ['waiting', 'assigned', 'on_delivery'])->count(),
            ];
        });
    }

    /**
     * Get recent activities for a user.
     */
    public function getRecentActivities(int $userId, int $limit = 5): array
    {
        return ActivityLog::where('user_id', $userId)
            ->latest('performed_at')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent orders for a user with status.
     */
    public function getRecentOrders(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('user_id', $userId)
            ->with(['items.menu'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get active orders (not completed/cancelled) for a user.
     */
    public function getActiveOrders(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('user_id', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['items.menu'])
            ->latest()
            ->get();
    }
}
