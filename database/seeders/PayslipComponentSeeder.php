<?php

namespace Database\Seeders;

use App\Models\PayslipComponent;
use Illuminate\Database\Seeder;

class PayslipComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // Pendapatan
            ['name' => 'Gaji Pokok', 'type' => 'earning'],
            ['name' => 'Tunjangan Jabatan', 'type' => 'earning'],
            ['name' => 'Tunjangan Komparatif', 'type' => 'earning'],
            ['name' => 'Tunjangan Transport', 'type' => 'earning'],
            ['name' => 'JAMSOSTEK', 'type' => 'earning'],
            ['name' => 'Lain-Lain', 'type' => 'earning'],
            ['name' => 'Uang Lembur', 'type' => 'earning'],
            
            // Potongan
            ['name' => 'Kas Bon', 'type' => 'deduction'],
            ['name' => 'Angsuran M.', 'type' => 'deduction'],
            ['name' => 'Ang. Tambahan', 'type' => 'deduction'],
            ['name' => 'Bunga', 'type' => 'deduction'],
            ['name' => 'JAMSOSTEK', 'type' => 'deduction'],
            ['name' => 'Lain-Lain', 'type' => 'deduction'],
        ];

        foreach ($components as $comp) {
            PayslipComponent::create($comp);
        }
    }
}