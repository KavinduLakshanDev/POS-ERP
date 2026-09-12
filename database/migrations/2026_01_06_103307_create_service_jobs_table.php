<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('job_number')->unique();
            $table->string('invoice_number')->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->integer('AccKy')->nullable()->comment('Customer ID from acc_mas');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_vat_no', 50)->nullable();

            $table->string('device_model')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_serial')->nullable();
            $table->string('device_barcode')->nullable();
            $table->string('device_warranty')->nullable();
            $table->text('problem_description');

            $table->date('received_date');
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->date('delivered_date')->nullable();

            $table->integer('assigned_technician_id')->nullable()->comment('User ID of technician');
            $table->string('technician_name')->nullable();

            $table->enum('status', [
                'pending',
                'assigned',
                'in_progress',
                'waiting_for_parts',
                'completed',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->decimal('total_service_charge', 18, 2)->default(0);
            $table->decimal('total_parts_cost', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->boolean('is_vat_invoice')->default(false);
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->decimal('subtotal_before_vat', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('balance_amount', 18, 2)->default(0);
            $table->decimal('advanced_payment', 15, 2)->default(0);

            $table->text('technician_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('company_code', 255)->nullable();
            $table->string('section_code', 255)->nullable();
            $table->string('company_vat_no', 50)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->comment('User ID who created the job');

            $table->timestamps();
            $table->softDeletes();

            $table->index('AccKy');
            $table->index('assigned_technician_id');
            $table->index('status');
            $table->index('job_number');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_jobs');
    }
};
