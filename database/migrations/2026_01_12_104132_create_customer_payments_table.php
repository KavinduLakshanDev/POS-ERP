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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->unsigned();
            $table->unsignedBigInteger('service_job_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('method', 50); // Cash, Cheque, Card, Credit, Bank Transfer, cash_refund
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('card_auth_code')->nullable();
            $table->string('reference')->nullable();
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('selected_bank_id')->nullable();
            $table->boolean('is_deposited')->default(false);
            $table->timestamp('deposited_at')->nullable();
            $table->unsignedBigInteger('deposit_bank_id')->nullable();
            $table->json('invoice_allocations')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('AdrKy')->on('address')->onDelete('cascade');
            $table->foreign('service_job_id')->references('id')->on('service_jobs')->onDelete('set null');
            $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('selected_bank_id')->references('id')->on('bank_accounts')->nullOnDelete();
            $table->foreign('deposit_bank_id')->references('id')->on('bank_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
