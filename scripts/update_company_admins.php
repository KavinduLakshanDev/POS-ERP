<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating Company Admins to remove section assignments...\n";
echo str_repeat("=", 80) . "\n\n";

// Update Vismass admin
$vismassAdmin = App\Models\User::where('email', 'company_admin@example.com')->first();
if ($vismassAdmin) {
    $vismassAdmin->section_code = null;
    $vismassAdmin->save();
    echo "✓ Updated company_admin@example.com - Section removed\n";
}

echo "\nVerification:\n";
echo str_repeat("-", 80) . "\n";

$companyAdmins = App\Models\User::where('user_type', 'company_admin')
    ->orWhere('user_type', 'company_user')
    ->get();

foreach ($companyAdmins as $admin) {
    echo "Email: {$admin->email}\n";
    echo "  Company: {$admin->company_code}\n";
    echo "  Section: " . ($admin->section_code ?? 'None (✓)') . "\n";
    echo "  Role: " . ($admin->role ? $admin->role->name : 'No Role') . "\n";
    echo str_repeat("-", 80) . "\n";
}
