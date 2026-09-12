<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get company-specific Roles (used instead of global roles so that
        //    Vismass and Malibu cashiers can have independent permission sets)
        $companyAdminRole   = Role::where('slug', 'company_admin')->first()
                               ?? Role::where('slug', 'company_admin')->first();
        $technicianRole     = Role::where('slug', 'technician')->first()
                               ?? Role::where('slug', 'technician')->first();
        $cashierRole        = Role::where('slug', 'cashier')->first()
                               ?? Role::where('slug', 'cashier')->first();
        $salesRepRole       = Role::where('slug', 'sales_rep')->first()
                               ?? Role::where('slug', 'sales_rep')->first();

        // 2. Define Companies and Sections
        // Vismass Sections
        $serviceSection = Section::where('section_code', 'C1-SEC-001')->first();
        $importSection = Section::where('section_code', 'C1-SEC-002')->first();
        $mainStockSection = Section::where('section_code', 'C1-SEC-003')->first();

        // --- VISMASS (C1) COMPANY ADMIN ---
        if ($companyAdminRole) {
            User::firstOrCreate(
                ['email' => 'company1_admin@example.com'],
                [
                    'username' => 'company1_admin',
                    'password' => Hash::make('password'),
                    'first_name' => 'Company1',
                    'last_name' => 'Admin',
                    'user_type' => 'company_admin',
                    'role_id' => $companyAdminRole->id,
                    'company_code' => 'C1',
                    'section_code' => 'C1-SEC-002',
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        // --- VISMASS SECTION USERS ---

        // Technicians (Assigned to Service Section)
        if ($technicianRole && $serviceSection) {
            // Create 2 technicians for Vismass
            for ($i = 1; $i <= 2; $i++) {
                User::firstOrCreate(
                    ['email' => "tech{$i}.company1@example.com"],
                    [
                        'username' => "company1_tech_{$i}",
                        'password' => Hash::make('password'),
                        'first_name' => 'Company1',
                        'last_name' => "Technician {$i}",
                        'user_type' => 'company_user',
                        'role_id' => $technicianRole->id,
                        'company_code' => 'C1',
                        'section_code' => $serviceSection->section_code,
                        'is_active' => true,
                        'is_verified' => true,
                        'email_verified_at' => now(),
                        'uuid' => (string) Str::uuid(),
                    ]
                );
            }
        }

        // Cashiers (Assigned to Import Section)
        if ($cashierRole && $importSection) {
            // Create 2 cashiers for Vismass
            for ($i = 1; $i <= 2; $i++) {
                User::firstOrCreate(
                    ['email' => "cashier{$i}.company1@example.com"],
                    [
                        'username' => "company1_cashier_{$i}",
                        'password' => Hash::make('password'),
                        'first_name' => 'Company1',
                        'last_name' => "Cashier {$i}",
                        'user_type' => 'company_user',
                        'role_id' => $cashierRole->id,
                        'company_code' => 'C1',
                        'section_code' => $importSection->section_code,
                        'is_active' => true,
                        'is_verified' => true,
                        'email_verified_at' => now(),
                        'uuid' => (string) Str::uuid(),
                    ]
                );
            }
        }

        // Sales Reps (Assigned to Main Stock)
        if ($salesRepRole && $mainStockSection) {
            User::firstOrCreate(
                ['email' => 'sales1.company1@example.com'],
                [
                    'username' => 'company1_sales_1',
                    'password' => Hash::make('password'),
                    'first_name' => 'Company1',
                    'last_name' => 'Sales Rep 1',
                    'user_type' => 'company_user',
                    'role_id' => $salesRepRole->id,
                    'company_code' => 'C1',
                    'section_code' => $mainStockSection->section_code,
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }
    }
}
