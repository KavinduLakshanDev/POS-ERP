<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itemmaster', function (Blueprint $table) {
            $table->string('ItmKy', 50)->primary();
            $table->string('ItemCode', 50);
            $table->string('BarCode', 100)->nullable();
            $table->string('batch_no', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('warranty', 100)->nullable();
            $table->string('ItmNm', 200)->nullable();
            $table->string('Unit', 20)->nullable();
            $table->decimal('PackQty', 15, 4)->default(0);
            $table->decimal('CosPri', 15, 4)->default(0);
            $table->decimal('SlsPri', 15, 4)->default(0);
            $table->decimal('NCostPrice', 15, 4)->default(0);
            $table->decimal('ExtraPrice', 15, 4)->default(0);
            $table->decimal('WholePrice', 15, 4)->default(0);
            $table->decimal('CCPrice', 15, 4)->default(0);
            $table->string('catkey', 50)->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('models_id')->nullable();
            $table->integer('UnitKy')->nullable();
            $table->unsignedInteger('transfer_unit_id')->nullable();
            $table->unsignedInteger('receiving_unit_id')->nullable();
            $table->decimal('transfer_conversion_factor', 15, 4)->default(1.0000);
            $table->integer('ReOrdlLvl')->default(0);
            $table->boolean('ScallItem')->default(false);
            $table->integer('SupKey')->nullable();
            $table->integer('RtQty1')->default(0);
            $table->decimal('RtDis1', 15, 4)->default(0);
            $table->integer('RtQty2')->default(0);
            $table->decimal('RtDis2', 15, 4)->default(0);
            $table->integer('RtQty3')->default(0);
            $table->decimal('RtDis3', 15, 4)->default(0);
            $table->integer('RtQty4')->default(0);
            $table->decimal('RtDis4', 15, 4)->default(0);
            $table->integer('DiscountQty')->default(0);
            $table->decimal('QuntityDiscount', 15, 4)->default(0);
            $table->boolean('VATItem')->default(false);
            $table->boolean('is_service')->default(false);
            $table->boolean('fInAct')->default(false);
            $table->string('company_code', 20);
            $table->string('section_code', 20);
            $table->string('Status', 1)->default('A');
            $table->enum('item_type', ['product', 'printer', 'service'])->default('product');
            $table->json('available_sections')->nullable();
            $table->json('available_business_units')->nullable();
            $table->uuid('uuid')->nullable();
            $table->integer('free_issue_scheme_buy_qty')->nullable();
            $table->integer('free_issue_scheme_get_qty')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('models_id')->references('id')->on('models')->onDelete('set null');

            $table->index('BarCode');
            $table->index('brand_id');
            $table->index('models_id');
            $table->index('Status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itemmaster');
    }
};
