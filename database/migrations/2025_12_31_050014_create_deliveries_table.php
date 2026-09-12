<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number');
            $table->foreignId('delivery_route_id')->constrained('delivery_routes');
            $table->foreignId('distributor_id')->nullable()->constrained('users');
            $table->string('status')->default('pending');
            $table->string('company_code');
            $table->string('customer_name');
            $table->string('customer_address');
            $table->string('customer_phone');
            $table->string('discount_type')->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('section_code', 50)->nullable();
            $table->timestamps();

            $table->unique(['delivery_number', 'company_code'], 'deliveries_delivery_number_company_unique');
            $table->foreign('company_code')->references('company_code')->on('companies')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
            $table->index(['vehicle_id', 'shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
