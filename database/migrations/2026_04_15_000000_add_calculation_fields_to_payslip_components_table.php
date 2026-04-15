<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslip_components', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->nullable()->default(0)->after('type');
            $table->bigInteger('max_cap')->nullable()->after('percentage');
        });
    }

    public function down(): void
    {
        Schema::table('payslip_components', function (Blueprint $table) {
            $table->dropColumn(['percentage', 'max_cap']);
        });
    }
};
