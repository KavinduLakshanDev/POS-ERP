<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('from_section_code');
            $table->string('to_section_code');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('stock_id');
            $table->decimal('quantity', 10, 2);
            $table->unsignedInteger('sent_unit_id')->nullable();
            $table->unsignedInteger('received_unit_id')->nullable();
            $table->decimal('conversion_factor', 15, 4)->default(1.0000);
            $table->decimal('received_quantity', 15, 4)->nullable();
            $table->decimal('cost_price', 10, 2);
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->string('company_code');
            $table->string('batch_no', 255)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('warranty', 255)->nullable();
            $table->string('item_code', 50)->nullable();
            $table->string('item_name', 255)->nullable();
            $table->timestamps();

            $table->index(['from_section_code', 'to_section_code']);
            $table->index('item_id');
            $table->index('transfer_date');
            $table->index('stock_id');
            $table->index('item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
