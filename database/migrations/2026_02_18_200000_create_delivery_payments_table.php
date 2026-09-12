<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->restrictOnDelete();
            $table->decimal('amount', 15, 4);
            $table->string('method', 50)->default('cash');
            $table->enum('status', ['pending', 'cleared', 'bounced'])->default('cleared');
            $table->decimal('service_charge', 15, 4)->nullable();
            $table->foreignId('related_payment_id')->nullable()->constrained('delivery_payments')->nullOnDelete();
            $table->timestamp('bounced_at')->nullable();
            $table->boolean('is_deposited')->default(false);
            $table->date('deposited_at')->nullable();
            $table->foreignId('deposit_bank_id')->nullable()->constrained('bank_accounts');
            $table->string('reference_no')->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('cheque_no', 20)->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_code', 10);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_payments');
    }
};
