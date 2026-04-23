<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;

class EmailPreviewController extends Controller
{
    public function payslip(Request $request)
    {
        $payslipId = (string) $request->query('payslip_id', '');

        $query = Payslip::query()->with(['employee', 'payrollPeriod', 'details.component']);

        $payslip = $payslipId !== ''
            ? $query->where('id', $payslipId)->first()
            : $query->latest('created_at')->first();

        if (!$payslip) {
            abort(404, 'Payslip tidak ditemukan.');
        }

        return view('emails.payslip', ['payslip' => $payslip]);
    }
}
