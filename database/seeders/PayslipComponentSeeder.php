<?php

namespace Database\Seeders;

use App\Models\PayslipComponent;
use Illuminate\Database\Seeder;

class PayslipComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['name' => 'Gaji Pokok', 'type' => 'earning', 'percentage' => null, 'max_cap' => null],
            ['name' => 'Lembur dan Bonus', 'type' => 'earning', 'percentage' => null, 'max_cap' => null],
            ['name' => 'Jaminan Hari Tua', 'type' => 'deduction', 'percentage' => 5.70],
            ['name' => 'Jaminan Kecelakaan Kerja', 'type' => 'deduction', 'percentage' => 0.54],
            ['name' => 'Jaminan Kematian', 'type' => 'deduction', 'percentage' => 0.30],
            ['name' => 'PPh Pasal 21', 'type' => 'tax', 'percentage' => null, 'max_cap' => null],
        ];

        foreach ($components as $comp) {
            PayslipComponent::create($comp);
        }
    }
}