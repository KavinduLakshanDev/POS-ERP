<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            ControlMasterSeeder::class,
            VatRateSeeder::class,
            UsersSeeder::class,
        ]);

        // Super Admin
        User::firstOrCreate(
            ['email' => 'admin@unitec.lk'],
            [
                'username' => 'unitec_admin',
                'first_name' => 'Unitec',
                'last_name' => 'Admin',
                'password' => 'unitec@123Admin',
                'user_type' => 'super_admin',
                'role_id' => 1,
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
