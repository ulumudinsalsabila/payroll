<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PayslipTemplateImport implements ToArray, WithCalculatedFormulas
{
    public function array(array $array): void
    {
        // Intentionally left blank.
        // We use Excel::toArray() in the controller to retrieve raw worksheet rows.
    }
}
