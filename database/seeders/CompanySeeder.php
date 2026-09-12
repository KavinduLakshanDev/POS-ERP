<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Section;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['company_code' => 'VIS001'],
            [
                'name' => 'Test Company',
                'contact_person_name' => 'John Doe',
                'contact_person_number' => '0771234567',
                'email' => 'company_admin@example.com',
                'password' => 'password',
                'phone' => '0112345678',
                'address' => '123, Main Street',
                'city' => 'Colombo',
                'state' => 'Western',
                'country' => 'Sri Lanka',
                'postal_code' => '10100',
                'tax_id' => 'TAX123',
                'vat_rate' => 18.00,
                'vat_no' => '123456789V',
                'vat_effective_date' => now(),
            ]
        );

        Section::firstOrCreate(
            ['section_code' => 'VIS-SEC-001'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $company->company_code,
                'name' => 'Service',
                'section_type' => 'other',
            ]
        );

        Section::firstOrCreate(
            ['section_code' => 'VIS-SEC-002'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $company->company_code,
                'name' => 'Shop Stock',
                'section_type' => 'store',
            ]
        );

        Section::firstOrCreate(
            ['section_code' => 'VIS-SEC-003'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $company->company_code,
                'name' => 'Main Stock',
                'section_type' => 'store',
                'is_main_stock' => true,
            ]
        );
    }
}
