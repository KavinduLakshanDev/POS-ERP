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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->integer('supplier_id')->unsigned();
            $table->string('supplier_code');
            $table->string('supplier_name');
            $table->text('supplier_address')->nullable();
            $table->string('supplier_tel')->nullable();
            $table->string('payment_method');
            $table->decimal('paid_amount', 15, 2);
            $table->json('invoice_allocations')->nullable();
            $table->date('payment_date');
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('cheque_account_no')->nullable();
            $table->string('cheque_bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_reference_no')->nullable();
            $table->date('bank_deposit_date')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('transfer_bank_name')->nullable();
            $table->string('transfer_transaction_id')->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('transfer_reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('selected_bank_id')->nullable();
            $table->string('company_code');
            $table->string('section_code')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('AdrKy')->on('address')->onDelete('cascade');
            $table->foreign('selected_bank_id')->references('id')->on('bank_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
