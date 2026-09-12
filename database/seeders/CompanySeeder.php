<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Section;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Main Company: Vismass
        $vismass = Company::firstOrCreate(
            ['company_code' => 'C1'],
            [
                'name' => 'Company1',
                'contact_person_name' => 'John Doe',
                'contact_person_number' => '0771234567',
                'email' => 'company_admin@example.com',
                'password' => 'password', // Will be hashed by model cast
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

        // Create Company1 Sections
        Section::firstOrCreate(
            ['section_code' => 'C1-SEC-001'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $vismass->company_code,
                'name' => 'Service',
                'section_type' => 'other'
            ]
        );
        Section::firstOrCreate(
            ['section_code' => 'C1-SEC-002'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $vismass->company_code,
                'name' => 'Company1 shop stock',
                'section_type' => 'store'
            ]
        );
        Section::firstOrCreate(
            ['section_code' => 'C1-SEC-003'],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'company_code' => $vismass->company_code,
                'name' => 'Main Stock',
                'section_type' => 'store',
                'is_main_stock' => true
            ]
        );
    }
}
