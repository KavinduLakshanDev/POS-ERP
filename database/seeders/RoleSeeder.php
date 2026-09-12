<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ─── Global / system roles ────────────────────────────────────────────
        // These exist for super_admin and as fallback defaults.
        Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Has full access to the entire system',
                'level' => 'super_admin',
                'company_code' => null,
            ]
        );

        // ─── Company-specific roles ─────────────────────────
        // Permissions are assigned independently so C1 and MAL001 roles
        // can have different permission sets even when they share the same
        // "type" (e.g. cashier).
        Role::firstOrCreate(
            ['slug' => 'company_admin'],
            [
                'name' => 'Company Admin',
                'description' => 'Company admin for Vismass',
                'level' => 'company_admin',
                'company_code' => 'C1',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'technician'],
            [
                'name' => 'Technician',
                'description' => 'Technician for eservices',
                'level' => 'technician',
                'company_code' => 'C1',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'Cashier',
                'description' => 'Cashier for retail and eservices',
                'level' => 'cashier',
                'company_code' => 'C1',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'sales_rep'],
            [
                'name' => 'Sales Representative',
                'description' => 'Sales representative for retail and eservices',
                'level' => 'sales_rep',
                'company_code' => 'C1',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'service_manager'],
            [
                'name' => 'Service Manager',
                'description' => 'Service manager for Vismass',
                'level' => 'service_manager',
                'company_code' => 'C1',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'stock_manager'],
            [
                'name' => 'Stock Manager',
                'description' => 'Stock manager for retail and eservices',
                'level' => 'stock_manager',
                'company_code' => 'C1',
            ]
        );
    }
}
