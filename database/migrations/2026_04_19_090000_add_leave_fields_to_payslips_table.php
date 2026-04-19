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
        Schema::table('payslips', function (Blueprint $table) {
            $table->integer('leave_joint_days')->default(0)->after('work_days');
            $table->integer('leave_personal_days')->default(0)->after('leave_joint_days');
            $table->integer('leave_entitlement')->nullable()->after('leave_personal_days');
            $table->integer('leave_remaining')->nullable()->after('leave_entitlement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['leave_joint_days', 'leave_personal_days', 'leave_entitlement', 'leave_remaining']);
        });
    }
};
