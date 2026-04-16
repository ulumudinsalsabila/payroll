<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayslipComponent;
use App\Models\TerRate;

class PayrollService
{
    public function calculateForEmployee(string $employeeId, array $earningsInput): array
    {
        $employee = Employee::findOrFail($employeeId);

        $totalBruto = 0;
        foreach ($earningsInput as $val) {
            if ($val === null || $val === '') {
                continue;
            }
            $totalBruto += (int) round((float) $val);
        }

        $basicSalaryComponentId = PayslipComponent::query()
            ->where('is_active', true)
            ->where('type', 'earning')
            ->where('name', 'Gaji Pokok')
            ->value('id');

        $base = $totalBruto;
        if ($basicSalaryComponentId && array_key_exists($basicSalaryComponentId, $earningsInput)) {
            $basicVal = $earningsInput[$basicSalaryComponentId];
            $basicSalary = $basicVal === null || $basicVal === '' ? 0 : (int) round((float) $basicVal);
            if ($basicSalary > 0) {
                $base = $basicSalary;
            }
        }

        $deductionComponents = PayslipComponent::query()
            ->where('is_active', true)
            ->where('type', 'deduction')
            ->orderBy('name')
            ->get(['id', 'percentage', 'max_cap']);

        $deductions = [];
        $totalDeductions = 0;
        foreach ($deductionComponents as $comp) {
            $percentage = $comp->percentage === null ? 0.0 : (float) $comp->percentage;
            $calcBase = $base;
            if (!empty($comp->max_cap) && (int) $comp->max_cap > 0) {
                $calcBase = min($calcBase, (int) $comp->max_cap);
            }
            $amount = (int) round($calcBase * ($percentage / 100));
            $amount = max($amount, 0);
            $deductions[$comp->id] = $amount;
            $totalDeductions += $amount;
        }

        $tax = 0;
        $category = strtoupper((string) ($employee->ter_category ?? ''));
        if (in_array($category, ['A', 'B', 'C'], true)) {
            $rate = TerRate::query()
                ->where('category', $category)
                ->where('min_bruto', '<=', $totalBruto)
                ->where(function ($q) use ($totalBruto) {
                    $q->whereNull('max_bruto')->orWhere('max_bruto', '>=', $totalBruto);
                })
                ->orderByDesc('min_bruto')
                ->first();

            if ($rate) {
                $tax = (int) round($totalBruto * (((float) $rate->percentage) / 100));
                $tax = max($tax, 0);
            }
        }

        $taxComponents = PayslipComponent::query()
            ->where('is_active', true)
            ->where('type', 'tax')
            ->orderBy('name')
            ->get(['id', 'name']);

        $taxes = [];
        foreach ($taxComponents as $comp) {
            $taxes[$comp->id] = 0;
        }

        if ($tax > 0 && $taxComponents->count() > 0) {
            $target = $taxComponents->firstWhere('name', 'PPh Pasal 21') ?? $taxComponents->first();
            $taxes[$target->id] = $tax;
        }

        $totalTax = array_sum($taxes);
        $netto = (int) ($totalBruto - $totalDeductions - $totalTax);

        return [
            'deductions' => $deductions,
            'taxes' => $taxes,
            'netto' => $netto,
        ];
    }
}
