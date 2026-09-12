<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->string('ItmKy', 50);
            $table->string('batch_no', 50)->default('DEFAULT');
            $table->string('serial_number', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('warranty', 50)->nullable();
            $table->string('section_code', 20);
            $table->string('ItemCode', 50);
            $table->string('ItemName', 200);
            $table->string('Unit', 20)->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('returned_quantity', 10, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->timestamps();

            $table->index(['delivery_id', 'ItmKy', 'batch_no'], 'idx_delivery_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
    }
};
