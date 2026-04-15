<?php

namespace Database\Seeders;

use App\Models\PayslipComponent;
use Illuminate\Database\Seeder;

class PayslipComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['name' => 'Gaji Pokok', 'type' => 'earning'],
            ['name' => 'Lembur dan Bonus', 'type' => 'earning'],
            ['name' => 'Jaminan Hari Tua', 'type' => 'deduction'],
            ['name' => 'Jaminan Kecelakaan Kerja', 'type' => 'deduction'],
            ['name' => 'Jaminan Kematian', 'type' => 'deduction'],
            ['name' => 'PPh Pasal 21', 'type' => 'tax'],
        ];

        foreach ($components as $comp) {
            PayslipComponent::create($comp);
        }
    }
}