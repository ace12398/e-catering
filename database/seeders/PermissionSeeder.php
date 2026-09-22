<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view', 'dashboard.manage',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'vendors.view', 'vendors.create', 'vendors.update', 'vendors.delete',
            'menus.view', 'menus.create', 'menus.update', 'menus.delete',
            'orders.view', 'orders.create', 'orders.update', 'orders.cancel',
            'payments.view', 'payments.verify', 'payments.refund',
            'workspace.view', 'workspace.manage',
            'reports.view', 'reports.export',
            'deliveries.view', 'deliveries.manage',
            'system.settings'
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }
}
