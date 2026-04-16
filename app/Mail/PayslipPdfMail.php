<?php

namespace App\Mail;

use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payslip $payslip,
        public string $pdfContent,
        public string $fileName
    ) {
    }

    public function build(): static
    {
        $period = $this->payslip->payrollPeriod;
        $subject = 'Payslip ' . ($period?->month ?? '') . '-' . ($period?->year ?? '') . ' - ' . ($this->payslip->employee?->name ?? 'Karyawan');

        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($subject)
            ->view('emails.payslip', ['payslip' => $this->payslip])
            ->attachData($this->pdfContent, $this->fileName, ['mime' => 'application/pdf']);
    }
}
