<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->date('return_date');

            $table->integer('customer_id')->unsigned()->nullable();
            $table->string('customer_code')->nullable();
            $table->string('customer_name');

            $table->unsignedBigInteger('sales_transaction_id')->nullable();
            $table->string('original_invoice_no')->nullable();

            $table->string('section_code', 10);
            $table->string('company_code', 10);

            $table->enum('return_type', ['item', 'printer', 'mixed'])->default('item');
            $table->decimal('total_return_amount', 15, 2)->default(0);
            $table->decimal('return_value', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->decimal('exchange_amount', 15, 2)->default(0);

            $table->enum('refund_method', ['cash', 'exchange', 'credit_note'])->default('cash');
            $table->json('refund_details')->nullable();

            $table->enum('status', ['pending', 'processed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();

            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->foreign('customer_id')->references('AdrKy')->on('address')->onDelete('set null');
            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            $table->index('return_date');
            $table->index('customer_id');
            $table->index('section_code');
            $table->index('company_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_returns');
    }
};
