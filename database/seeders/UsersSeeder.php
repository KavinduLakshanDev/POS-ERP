<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $companyAdminRole = Role::where('slug', 'company_admin')->first();
        $technicianRole   = Role::where('slug', 'technician')->first();
        $cashierRole      = Role::where('slug', 'cashier')->first();
        $salesRepRole     = Role::where('slug', 'sales_rep')->first();

        $serviceSection = Section::where('section_code', 'VIS-SEC-001')->first();
        $shopSection    = Section::where('section_code', 'VIS-SEC-002')->first();
        $mainStock      = Section::where('section_code', 'VIS-SEC-003')->first();

        // ─── Main Company Admin ───────────────────────────────────────────────
        if ($companyAdminRole) {
            User::firstOrCreate(
                ['email' => 'company_admin@example.com'],
                [
                    'username' => 'company_admin',
                    'password' => Hash::make('password'),
                    'first_name' => 'Test',
                    'last_name' => 'Admin',
                    'user_type' => 'company_admin',
                    'role_id' => $companyAdminRole->id,
                    'company_code' => 'VIS001',
                    'section_code' => 'VIS-SEC-002',
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        // ─── Main Company Technician ──────────────────────────────────────────
        if ($technicianRole && $serviceSection) {
            User::firstOrCreate(
                ['email' => 'tech1@example.com'],
                [
                    'username' => 'tech_1',
                    'password' => Hash::make('password'),
                    'first_name' => 'Test',
                    'last_name' => 'Technician 1',
                    'user_type' => 'company_user',
                    'role_id' => $technicianRole->id,
                    'company_code' => 'VIS001',
                    'section_code' => $serviceSection->section_code,
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        // ─── Main Company Cashier ─────────────────────────────────────────────
        if ($cashierRole && $shopSection) {
            User::firstOrCreate(
                ['email' => 'cashier1@example.com'],
                [
                    'username' => 'cashier_1',
                    'password' => Hash::make('password'),
                    'first_name' => 'Test',
                    'last_name' => 'Cashier 1',
                    'user_type' => 'company_user',
                    'role_id' => $cashierRole->id,
                    'company_code' => 'VIS001',
                    'section_code' => $shopSection->section_code,
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        // ─── Main Company Sales Rep ───────────────────────────────────────────
        if ($salesRepRole && $mainStock) {
            User::firstOrCreate(
                ['email' => 'sales1@example.com'],
                [
                    'username' => 'sales_1',
                    'password' => Hash::make('password'),
                    'first_name' => 'Test',
                    'last_name' => 'Sales Rep 1',
                    'user_type' => 'company_user',
                    'role_id' => $salesRepRole->id,
                    'company_code' => 'VIS001',
                    'section_code' => $mainStock->section_code,
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }
    }
}
