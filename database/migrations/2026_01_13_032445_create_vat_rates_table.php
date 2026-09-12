<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('vat_rate', 5, 2);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('vat_no')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
