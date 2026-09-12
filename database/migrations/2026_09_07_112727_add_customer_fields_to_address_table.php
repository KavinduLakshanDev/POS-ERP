<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address', function (Blueprint $table) {
            if (!Schema::hasColumn('address', 'BRNo')) {
                $table->string('BRNo', 50)->nullable()->after('IDNo');
            }
            if (!Schema::hasColumn('address', 'TINNo')) {
                $table->string('TINNo', 50)->nullable()->after('BRNo');
            }
            if (!Schema::hasColumn('address', 'AddressLine2')) {
                $table->string('AddressLine2', 250)->nullable()->after('Address');
            }
            if (!Schema::hasColumn('address', 'Locality')) {
                $table->string('Locality', 100)->nullable()->after('AddressLine2');
            }
            if (!Schema::hasColumn('address', 'City')) {
                $table->string('City', 100)->nullable()->after('Locality');
            }
            if (!Schema::hasColumn('address', 'PostalCode')) {
                $table->string('PostalCode', 20)->nullable()->after('City');
            }
        });
    }

    public function down(): void
    {
        Schema::table('address', function (Blueprint $table) {
            $columnsToDrop = ['BRNo', 'TINNo', 'AddressLine2', 'Locality', 'City', 'PostalCode'];
            $existingColumns = array_filter($columnsToDrop, fn($col) => Schema::hasColumn('address', $col));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
