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
        Schema::create('payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payroll_period_id');
            $table->uuid('employee_id');
            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->date('payment_date');
            $table->integer('work_days')->default(0);
            $table->bigInteger('basic_salary')->default(0);
            $table->bigInteger('total_earnings')->default(0);
            $table->bigInteger('total_deductions')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('net_salary')->default(0);
            $table->string('status')->default('pending_review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
