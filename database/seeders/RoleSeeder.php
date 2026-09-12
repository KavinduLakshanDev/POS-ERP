<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ─── System Role ──────────────────────────────────────────────────────
        Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Has full access to the entire system',
                'level' => 'super_admin',
                'company_code' => null,
                'is_system_role' => true,
            ]
        );

        // ─── Vismass Roles ────────────────────────────────────────────────────
        Role::firstOrCreate(
            ['slug' => 'company_admin'],
            [
                'name' => 'Company Admin',
                'description' => 'Company admin for Vismass',
                'level' => 'company_admin',
                'company_code' => 'VIS001',
                'is_system_role' => true,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'technician'],
            [
                'name' => 'Technician',
                'description' => 'Technician for services',
                'level' => 'technician',
                'company_code' => 'VIS001',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'Cashier',
                'description' => 'Cashier for retail and services',
                'level' => 'cashier',
                'company_code' => 'VIS001',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'sales_rep'],
            [
                'name' => 'Sales Representative',
                'description' => 'Sales representative for retail and services',
                'level' => 'sales_rep',
                'company_code' => 'VIS001',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'service_manager'],
            [
                'name' => 'Service Manager',
                'description' => 'Service manager for Vismass',
                'level' => 'service_manager',
                'company_code' => 'VIS001',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'stock_manager'],
            [
                'name' => 'Stock Manager',
                'description' => 'Stock manager for retail and services',
                'level' => 'stock_manager',
                'company_code' => 'VIS001',
            ]
        );
    }
}
