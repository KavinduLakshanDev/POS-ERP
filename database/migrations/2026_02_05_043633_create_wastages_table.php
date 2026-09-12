<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wastages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('category')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->string('serial_number')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('warranty')->nullable();
            $table->unsignedBigInteger('purchase_det_id')->nullable();
            $table->unsignedBigInteger('stock_in_hand_id')->nullable();
            $table->string('reason');
            $table->date('wastage_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->index('section_id');
            $table->index('serial_number');
            $table->index('batch_no');
            $table->index('purchase_det_id');
            $table->index('stock_in_hand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wastages');
    }
};
