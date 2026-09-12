<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_price_det', function (Blueprint $table) {
            $table->bigIncrements('ItemPriceKey');
            $table->string('ItmKy', 50);
            $table->string('serial_number', 100)->nullable();
            $table->string('batch_no', 50)->nullable();
            $table->decimal('Price', 15, 4)->default(0);
            $table->decimal('PrevPrice', 15, 4)->default(0);
            $table->date('EffectiveDate')->nullable();
            $table->date('PriceDate')->nullable();
            $table->string('PriceType', 20)->default('RETAIL');
            $table->string('company_code', 20);
            $table->string('section_code', 20);
            $table->string('Status', 1)->default('A');
            $table->string('InUse', 1)->default('Y');
            $table->boolean('fInAct')->default(false);
            $table->decimal('CosPri', 15, 4)->default(0);
            $table->decimal('NCostPrice', 15, 4)->default(0);
            $table->decimal('SlsPri', 15, 4)->default(0);
            $table->decimal('WholePrice', 15, 4)->default(0);
            $table->decimal('RtQty1', 15, 4)->default(0);
            $table->decimal('RtDis1', 15, 4)->default(0);
            $table->decimal('RtQty2', 15, 4)->default(0);
            $table->decimal('RtDis2', 15, 4)->default(0);
            $table->decimal('RtQty3', 15, 4)->default(0);
            $table->decimal('RtDis3', 15, 4)->default(0);
            $table->decimal('RtQty4', 15, 4)->default(0);
            $table->decimal('RtDis4', 15, 4)->default(0);
            $table->decimal('DiscountQty', 15, 4)->default(0);
            $table->decimal('QuntityDiscount', 15, 4)->default(0);
            $table->decimal('CCPrice', 15, 4)->default(0);
            $table->decimal('ExtraPrice', 15, 4)->default(0);
            $table->dateTime('ChangedDate')->nullable();
            $table->string('uuid', 36)->nullable();
            $table->string('warranty', 100)->nullable();
            $table->timestamps();

            $table->index('EffectiveDate', 'idx_effective_date');
            $table->index(['ItmKy', 'batch_no'], 'idx_itmky_batch');
            $table->index(['company_code', 'section_code', 'batch_no'], 'idx_company_section_batch_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_price_det');
    }
};
