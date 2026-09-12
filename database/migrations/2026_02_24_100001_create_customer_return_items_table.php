<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_return_id');
            $table->enum('item_direction', ['in', 'out'])->default('in');

            $table->string('item_code', 50);
            $table->integer('item_ky')->nullable();
            $table->string('item_name');

            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->string('batch_no')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('warranty')->nullable();

            $table->enum('item_type', ['item', 'printer'])->default('item');

            $table->enum('condition', ['good', 'damaged', 'defective'])->default('good');
            $table->text('damage_notes')->nullable();
            $table->boolean('add_to_stock')->default(true);

            $table->string('stock_location_type', 20)->nullable();
            $table->string('stock_location_code', 50)->nullable();

            $table->unsignedBigInteger('original_sale_item_id')->nullable();

            $table->timestamps();

            $table->foreign('customer_return_id')->references('id')->on('customer_returns')->onDelete('cascade');
            $table->foreign('original_sale_item_id')->references('id')->on('sales_transaction_items')->onDelete('set null');

            $table->index('item_code');
            $table->index('serial_number');
            $table->index('batch_no');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_return_items');
    }
};
