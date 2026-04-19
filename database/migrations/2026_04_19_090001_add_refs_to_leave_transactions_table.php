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
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->uuid('payroll_period_id')->nullable()->after('employee_id');
            $table->uuid('payslip_id')->nullable()->after('payroll_period_id');

            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->nullOnDelete();
            $table->foreign('payslip_id')->references('id')->on('payslips')->nullOnDelete();

            $table->index(['payroll_period_id']);
            $table->index(['payslip_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropForeign(['payslip_id']);

            $table->dropIndex(['payroll_period_id']);
            $table->dropIndex(['payslip_id']);

            $table->dropColumn(['payroll_period_id', 'payslip_id']);
        });
    }
};
