<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoicesExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new InvoiceSheetExport('pemasukan', 'Pemasukan'),
            new InvoiceSheetExport('pengeluaran', 'Pengeluaran'),
        ];
    }
}
