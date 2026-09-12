<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->string('company_code', 255)->nullable();
            $table->string('section_code', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code', 'company_code'], 'models_code_company_unique');
            $table->index(['company_code', 'section_code']);
            $table->index('is_active');
            $table->index('brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
