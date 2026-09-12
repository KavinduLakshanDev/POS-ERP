<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_no')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('company_code')->index();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['company_code', 'assigned_user_id'], 'vehicles_company_assigned_user_unique');
            $table->unique(['company_code', 'name'], 'vehicles_company_name_unique');
            $table->unique(['company_code', 'registration_no'], 'vehicles_company_reg_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
