<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidated from 14 migration files into a single create migration.
     */
    public function up(): void
    {
        Schema::create('purchase_det', function (Blueprint $table) {
            $table->string('company_code', 20)->nullable();
            $table->string('section_code', 20)->nullable();

            $table->integer('PerchaseDetKy')->primary()->comment('Purchase Detail Key');
            $table->boolean('flnAct')->nullable()->comment('Inactive flag');
            $table->string('Status', 2)->nullable()->comment('Status');
            $table->integer('PurchaseKey')->comment('Purchase Key - FK to purchase table');
            $table->integer('iTimKy')->comment('Item Key');
            $table->float('Qty')->nullable()->comment('Quantity');
            $table->decimal('iTimDiscount', 18, 0)->nullable()->comment('Item Discount');
            $table->decimal('CostPrice', 19, 4)->nullable()->comment('Cost Price');
            $table->decimal('SalePrice', 19, 4)->nullable()->comment('Sale Price');
            $table->float('DiscountRate')->nullable()->comment('Discount Rate');
            $table->float('CusDiscountRate')->nullable()->comment('Customer Discount Rate (%)');
            $table->string('discount_type')->nullable()->comment('fixed or percentage');
            $table->decimal('Free', 18, 0)->nullable()->comment('Free Quantity');
            $table->decimal('AmountF', 19, 4)->nullable()->comment('Amount');
            $table->decimal('NormalCost', 19, 4)->nullable()->comment('Normal Cost');
            $table->decimal('WholePrice', 19, 4)->nullable()->comment('Wholesale Price');
            $table->decimal('VehicleSalePrice', 19, 4)->nullable()->comment('Vehicle Sale Price');
            $table->decimal('NewCostPrice', 19, 4)->nullable()->comment('New Cost Price');
            $table->timestamps();

            $table->string('batch_no', 50)->nullable()->comment('Batch Number');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('warranty', ['3months', '6months', '1year', '2years', '5years'])->nullable();
            $table->string('barcode', 100)->nullable();
            $table->enum('stock_location_type', ['main_stock', 'printing_section'])->nullable();
            $table->boolean('VATItem')->default(false);
            $table->text('remark')->nullable();

            $table->foreign('PurchaseKey')->references('PurchaseKey')->on('purchase')->onDelete('cascade');
            $table->unique(['company_code', 'section_code', 'serial_number'], 'idx_unique_serial_per_section');

            $table->index('PurchaseKey');
            $table->index('iTimKy');
            $table->index(['Status', 'flnAct']);
            $table->index(['company_code', 'section_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_det');
    }
};
