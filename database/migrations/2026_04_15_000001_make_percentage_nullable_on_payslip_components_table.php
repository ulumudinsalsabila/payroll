<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE payslip_components MODIFY percentage DECIMAL(5,2) NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE payslip_components ALTER COLUMN percentage DROP NOT NULL');
            DB::statement('ALTER TABLE payslip_components ALTER COLUMN percentage SET DEFAULT 0');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE payslip_components MODIFY percentage DECIMAL(5,2) NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE payslip_components ALTER COLUMN percentage SET NOT NULL');
            DB::statement('ALTER TABLE payslip_components ALTER COLUMN percentage SET DEFAULT 0');
        }
    }
};
