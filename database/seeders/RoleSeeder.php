<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::findOrCreate('Admin', 'web');
        $admin->givePermissionTo(Permission::all());

        $customer = Role::findOrCreate('Customer', 'web');
        $customer->givePermissionTo(['dashboard.view', 'menus.view', 'orders.create', 'orders.view', 'workspace.view', 'workspace.manage']);
    }
}
