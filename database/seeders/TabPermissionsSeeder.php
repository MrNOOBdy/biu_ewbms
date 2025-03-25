<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class TabPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'name' => 'Access Dashboard',
                'slug' => 'access-dashboard',
                'description' => 'Permission to access the dashboard tab'
            ],
            [
                'name' => 'Access Consumers',
                'slug' => 'access-consumers',
                'description' => 'Permission to access the consumers management tab'
            ],
            [
                'name' => 'Access Connection Payment',
                'slug' => 'access-connection-payment',
                'description' => 'Permission to access connection payment features'
            ],
            [
                'name' => 'Access Billing',
                'slug' => 'access-billing',
                'description' => 'Permission to access billing features'
            ],
            [
                'name' => 'Access Meter Reading',
                'slug' => 'access-meter-reading',
                'description' => 'Permission to access meter reading features'
            ],
            [
                'name' => 'Access Reports',
                'slug' => 'access-reports',
                'description' => 'Permission to access reports section'
            ],
            [
                'name' => 'Access Settings',
                'slug' => 'access-settings',
                'description' => 'Permission to access general settings'
            ],
            [
                'name' => 'Access Utilities',
                'slug' => 'access-utilities',
                'description' => 'Permission to access utilities section'
            ],
            [
                'name' => 'View User Management',
                'slug' => 'view-user-management',
                'description' => 'Access to user accounts section'
            ],
            [
                'name' => 'View Role Management',
                'slug' => 'view-role-management',
                'description' => 'Access to role management section'
            ],
            [
                'name' => 'View Permissions',
                'slug' => 'view-permissions',
                'description' => 'Access to permissions section'
            ],
            [
                'name' => 'View Dashboard',
                'slug' => 'view-dashboard',
                'description' => 'Access to dashboard section'
            ],
            [
                'name' => 'View Settings',
                'slug' => 'view-settings',
                'description' => 'Access to general settings'
            ],
            [
                'name' => 'View Reports',
                'slug' => 'view-reports',
                'description' => 'Access to reports section'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
