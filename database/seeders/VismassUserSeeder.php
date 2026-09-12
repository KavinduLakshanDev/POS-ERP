<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VismassUserSeeder extends Seeder
{
    public function run(): void
    {
        $companyAdminRole = Role::where('slug', 'company_admin')->first();
        $malDeliverySection = Section::where('section_code', 'MAL-SEC-001')->first();

        // ─── Child Company Admin ──────────────────────────────────────────────
        if ($companyAdminRole) {
            User::firstOrCreate(
                ['email' => 'company2_admin@example.com'],
                [
                    'username' => 'company2_admin',
                    'password' => 'password',
                    'first_name' => 'Test 2',
                    'last_name' => 'Admin',
                    'user_type' => 'company_admin',
                    'role_id' => $companyAdminRole->id,
                    'company_code' => 'MAL001',
                    'section_code' => $malDeliverySection?->section_code,
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }
    }
}
