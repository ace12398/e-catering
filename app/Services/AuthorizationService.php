<?php

namespace App\Services;

use App\Models\User;

class AuthorizationService
{
    public function canViewDashboard(User $user): bool
    {
        return $user->can('dashboard.view') || $user->isAdmin();
    }

    public function canManageOrders(User $user): bool
    {
        return $user->can('orders.update') || $user->isAdmin();
    }

    public function canManageWorkspace(User $user): bool
    {
        return $user->can('workspace.manage') || $user->isAdmin() || $user->isCustomer();
    }

    public function canManageMenus(User $user): bool
    {
        return $user->can('menus.create') || $user->isAdmin();
    }
}
