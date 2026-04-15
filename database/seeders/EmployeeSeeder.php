<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'employee_code' => 'ALT001',
            'name' => 'Akhmad Fauzi',
            'position' => 'QA (Quality Assurance)',
            'department' => 'Teknis',
            'address' => 'Jl. Purnawirawan, Palam, Cempaka, Kota Banjarbaru',
            'join_date' => '2024-05-01',
            'ptkp_status' => 'TK/1',
            'ter_category' => 'A',
            'leave_balance' => 4,
        ]);

        Employee::create([
            'employee_code' => 'ALT002',
            'name' => 'Rifky Lovanto',
            'position' => 'Backend Developer',
            'department' => 'Teknis',
            'address' => 'Jl. Mampang Indah I, Pancoran Mas, Depok',
            'join_date' => '2024-01-01',
            'ptkp_status' => 'TK/1',
            'ter_category' => 'A',
            'leave_balance' => 4,
        ]);
    }
}
