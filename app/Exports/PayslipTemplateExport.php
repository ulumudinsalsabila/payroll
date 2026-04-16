<?php

namespace App\Exports;

use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayslipTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    private Collection $employees;
    private Collection $components;
    private ?PayrollPeriod $previousPeriod;

    private array $workDaysByEmployee = [];
    private array $nettoByEmployee = [];
    private array $amountsByEmployee = [];

    private ?string $pph21ComponentId = null;

    public function __construct($employees, $components, $previousPeriod = null)
    {
        $this->employees = $employees instanceof Collection ? $employees : collect($employees);
        $this->components = $components instanceof Collection ? $components : collect($components);
        $this->previousPeriod = $previousPeriod;

        $this->pph21ComponentId = $this->components
            ->where('name', 'PPh Pasal 21')
            ->pluck('id')
            ->first();

        if ($this->previousPeriod) {
            $payslips = Payslip::query()
                ->where('payroll_period_id', $this->previousPeriod->id)
                ->with('details')
                ->get();

            foreach ($payslips as $payslip) {
                $this->workDaysByEmployee[$payslip->employee_id] = (int) ($payslip->work_days ?? 0);
                $this->nettoByEmployee[$payslip->employee_id] = (int) ($payslip->net_salary ?? 0);
                foreach ($payslip->details as $detail) {
                    $this->amountsByEmployee[$payslip->employee_id][$detail->payslip_component_id] = (int) ($detail->amount ?? 0);
                }

                if ($this->pph21ComponentId && !isset($this->amountsByEmployee[$payslip->employee_id][$this->pph21ComponentId]) && $payslip->tax_amount !== null) {
                    $this->amountsByEmployee[$payslip->employee_id][$this->pph21ComponentId] = (int) ($payslip->tax_amount ?? 0);
                }
            }
        }
    }

    public function headings(): array
    {
        $headings = ['Kode Karyawan', 'Nama Karyawan', 'Hari Kerja'];

        foreach ($this->components as $component) {
            $headings[] = (string) $component->name;
        }

        $headings[] = 'Netto';

        return $headings;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->employees as $employee) {
            $row = [
                (string) $employee->employee_code,
                (string) $employee->name,
                $this->previousPeriod ? ($this->workDaysByEmployee[$employee->id] ?? 0) : 0,
            ];

            foreach ($this->components as $component) {
                if ($this->previousPeriod) {
                    $row[] = $this->amountsByEmployee[$employee->id][$component->id] ?? 0;
                } else {
                    $row[] = 0;
                }
            }
            $row[] = $this->previousPeriod ? ($this->nettoByEmployee[$employee->id] ?? 0) : 0;

            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $colCount = 3 + $this->components->count() + 1;
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $range = 'A1:' . $lastCol . '1';

        $sheet->freezePane('A2');

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        return [];
    }
}
