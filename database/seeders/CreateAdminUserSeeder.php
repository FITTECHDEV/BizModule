<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'ID' => guid(),
            'EmployeeCode' => '1',
            // 'uuid' => guid(),
            'FirstName' => 'Super',
            'LastName' => 'Admin',
            'LoginID'  => '1234',
            'Password' => bcrypt('password'),
            'OfficeID' => '6785D4E7-558C-49F3-A172-4C0761116B43',
            'OfficeCode'=> 'UPS',
            'Role' => 1,
            'Email'=> 'superAdmin@example.com',
        ]);

        // $user = User::where('ID','1CA87704-2994-4999-8AA2-BEAC4A7F88FB')->first();

        $role = Role::create(['name' => 'SuperAdmin']);
        // $role = Role::where('id', 3)->first();

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
