<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in_hand', function (Blueprint $table) {
            $table->id('TableKy');
            $table->string('RefNo')->nullable();
            $table->unsignedBigInteger('Cky')->nullable();
            $table->string('company_code')->nullable();
            $table->string('owner_company_code', 10)->nullable(false)->index();
            $table->string('section_code')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->date('OrdDate')->nullable();
            $table->unsignedBigInteger('ItemKy')->nullable();
            $table->unsignedInteger('UnitKy')->nullable();
            $table->decimal('Qty', 15, 2)->default(0);
            $table->decimal('FreeQty', 15, 2)->default(0);
            $table->string('TrnTyp', 10)->nullable();
            $table->unsignedBigInteger('OrdKy')->nullable();
            $table->unsignedBigInteger('StkKy')->nullable();
            $table->unsignedBigInteger('OrdTypKy')->nullable();
            $table->unsignedBigInteger('CounterID')->nullable();
            $table->timestamps();

            $table->string('batch_no', 50)->nullable();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->enum('warranty', ['3months', '6months', '12 months', '1 year', '2 years', '5 years'])->nullable();

            $table->index(['company_code', 'section_code', 'ItemKy']);
            $table->index(['OrdDate']);
            $table->index(['TrnTyp']);
            $table->index(['OrdKy']);
            $table->index(['company_code', 'section_code', 'batch_no'], 'idx_stock_company_section_batch');
            $table->index(['ItemKy', 'vehicle_id']);
            $table->index(['ItemKy', 'UnitKy']);

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in_hand');
    }
};
