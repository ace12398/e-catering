<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(
        protected DashboardRepository $dashboardRepo,
        protected AdaptiveEngine $adaptiveEngine
    ) {}

    public function getDashboardData(User $user): array
    {
        $ctx             = $this->adaptiveEngine->getContext($user);
        $quickActions    = $this->adaptiveEngine->getQuickActions($ctx);
        $recommendations = $this->adaptiveEngine->getRecommendations($ctx);

        $analytics = $ctx['is_admin']
            ? $this->dashboardRepo->getAdminAnalytics()
            : $this->dashboardRepo->getAnalytics($user->id);

        $recentOrders  = $this->dashboardRepo->getRecentOrders($user->id);
        $activeOrders  = $this->dashboardRepo->getActiveOrders($user->id);

        $pref = \App\Models\WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $layoutMatrix = $pref->effective_layout_matrix;

        return [
            'user'               => $user,
            'ctx'                => $ctx,
            'analytics'          => $analytics,
            'recent_activities'  => $this->dashboardRepo->getRecentActivities($user->id),
            'recent_orders'      => $recentOrders,
            'active_orders'      => $activeOrders,
            'recommendations'    => $recommendations,
            'quick_actions'      => $quickActions,
            'profile_completion' => $ctx['profile_completion'],
            'layout_matrix'      => $layoutMatrix,
            'preference'         => $pref,
        ];
    }
}

