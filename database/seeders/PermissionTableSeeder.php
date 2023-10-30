<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionGroups = [
            // 'role-list',
            // 'role-create',
            // 'role-edit',
            // 'role-delete',
            // 'roles-list',

            // 'users-list',
            // 'admin-view',

            // 'change-user-password',
            // 'assign',

            // 'manage-category',

            // 'all-tickets-list',
            // 'ticket-raise',
            // 'my-ticket-list',
            // 'view-assigned-tickets',
            // 'view-ticket-reports',
            // 'view-my-office-tickets',
            // 'raise-ticket-for-any-office',
            // 'view-ticket-reports',
            // 'view-ticket-dashboard',

            // 'view-client-party',
            // 'create-client-party',
            // 'edit-client-party',
            // 'update-client-party',
            // 'create-client-for-any-office',
            // 'request-client-party',
            // [
            //     'module_name'=>'Entity Creation',
            //     'name'=>'delete-entity-request'
            // ],
            // [
            //     'module_name'=>'Entity Creation',
            //     'name'=>'update-entity-request
            //     '
            // ]
        ];

        // foreach ($permissionGroups as $permission) {
        //         Permission::create([
        //             // 'module_name' => $permission['module_name'],
        //             'name' => $permission
        //             ]);

        // }
    }
}
