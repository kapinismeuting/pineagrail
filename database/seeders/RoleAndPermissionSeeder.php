<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission agar data baru langsung terbaca
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission Granular
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.manage',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Buat Role utama
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Set permission untuk Admin & Customer
        $adminRole->syncPermissions([
            'users.view',
            'products.view',
            'products.create',
            'products.edit',
        ]);

        $customerRole->syncPermissions([
            'products.view',
        ]);

        // 4. Buat Akun Superadmin Pertama untuk Pineagrail
        $superadmin = User::firstOrCreate(
            ['email' => 'sa@pineagrail.com'],
            [
                'name' => 'Superadmin Pineagrail',
                'username' => 'superadmin',
                'password' => Hash::make('Pass312897!@'),
                'email_verified_at' => now(),
            ]
        );

        // Assign role Superadmin ke akun
        $superadmin->assignRole($superadminRole);
    }
}
