<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidated from 10 migration files into a single create migration.
     */
    public function up(): void
    {
        Schema::create('purchase', function (Blueprint $table) {
            $table->string('company_code', 20)->nullable();
            $table->string('section_code', 20)->nullable();

            $table->integer('PurchaseKey')->primary()->comment('Purchase Key (int)');
            $table->boolean('flnact')->nullable()->comment('Inactive flag');
            $table->string('Status', 2)->nullable()->comment('Status');
            $table->integer('PurchaseNo')->comment('Purchase Number');
            $table->dateTime('GRNDate')->nullable()->comment('GRN Date (renamed from Date)');
            $table->string('SuppCode', 50)->nullable()->comment('Supplier Code');
            $table->decimal('CostTotal', 19, 4)->nullable()->comment('Cost Total');
            $table->decimal('TotalVal', 19, 4)->nullable()->comment('Total Value');
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->decimal('ToIDscount', 19, 4)->nullable()->comment('Total Discount');
            $table->boolean('flused')->nullable()->comment('Used flag');
            $table->string('AddUser', 15)->nullable()->comment('User who added');
            $table->integer('OrdTypKy')->nullable()->comment('Order Type Key');
            $table->integer('AccKy')->nullable()->comment('Account Key');
            $table->string('Des', 100)->nullable()->comment('Description');
            $table->integer('ExtOrdKy')->nullable()->comment('External Order Key');
            $table->integer('CounterID')->nullable()->comment('Counter ID');
            $table->string('PayTrmKy', 50)->nullable()->comment('Payment Term Key');
            $table->decimal('TaxAmount', 19, 4)->nullable()->comment('Tax Amount');
            $table->string('CusOrdNo', 20)->nullable()->comment('Customer Order No');
            $table->string('SuppInvNo', 25)->nullable()->comment('Supplier Invoice No');
            $table->string('batch_no', 50)->nullable()->comment('Batch Number');
            $table->enum('type', ['supplier', 'transfer', 'adjustment'])->default('supplier');
            $table->string('barcode', 100)->nullable();
            $table->string('item_name', 255)->nullable()->comment('Representative item name for the purchase');
            $table->timestamps();

            $table->unique(['company_code', 'PurchaseNo'], 'purchase_company_number_unique');
            $table->index('PurchaseNo');
            $table->index('GRNDate');
            $table->index('SuppCode');
            $table->index(['Status', 'flnact']);
            $table->index(['company_code', 'section_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase');
    }
};
