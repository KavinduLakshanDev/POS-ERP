<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_type');
            $table->string('item_key')->nullable();
            $table->string('item_master_key')->nullable();
            $table->string('purchase_det_key')->nullable();
            $table->string('supplier_code');
            $table->string('supplier_invoice_no')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('return_value', 15, 2);
            $table->string('reason');
            $table->date('return_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('recorded_by');
            $table->unsignedBigInteger('section_id');
            $table->string('company_code', 10);
            $table->string('section_code', 50);
            $table->string('serial_number')->nullable();
            $table->string('batch_no')->nullable();
            $table->timestamps();

            $table->index('supplier_code');
            $table->index('return_type');
            $table->index('section_id');
            $table->index('recorded_by');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_returns');
    }
};
