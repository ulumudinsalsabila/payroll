<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    private $type;
    private $title;

    public function __construct(string $type, string $title)
    {
        $this->type = $type;
        $this->title = $title;
    }

    public function collection()
    {
        $invoices = Invoice::where('type', $this->type)->latest()->get();
        
        $grandSubtotal = $invoices->sum('subtotal');
        $grandTax = $invoices->sum('tax_amount');
        $grandDiscount = $invoices->sum('discount_amount');
        $grandTotal = $invoices->sum('total_amount');

        // Append grand total row object
        if ($invoices->count() > 0) {
            $invoices->push((object)[
                'is_grand_total' => true,
                'subtotal' => $grandSubtotal,
                'tax_amount' => $grandTax,
                'discount_amount' => $grandDiscount,
                'total_amount' => $grandTotal
            ]);
        }

        return $invoices;
    }

    public function headings(): array
    {
        return [
            'No Invoice',
            'Pelanggan',
            'Tanggal Invoice',
            'Tanggal Jatuh Tempo',
            'Status',
            'Subtotal',
            'Pajak',
            'Diskon',
            'Total Akhir',
            'Catatan'
        ];
    }

    public function map($invoice): array
    {
        if (isset($invoice->is_grand_total)) {
            return [
                '',
                '',
                '',
                '',
                'GRAND TOTAL',
                $invoice->subtotal,
                $invoice->tax_amount,
                $invoice->discount_amount,
                $invoice->total_amount,
                ''
            ];
        }

        return [
            $invoice->invoice_number,
            $invoice->customer_name,
            $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '',
            $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '',
            ucfirst($invoice->status),
            $invoice->subtotal,
            $invoice->tax_amount,
            $invoice->discount_amount,
            $invoice->total_amount,
            $invoice->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        return [
            1    => ['font' => ['bold' => true]],
            $highestRow => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
