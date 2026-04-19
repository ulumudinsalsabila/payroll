<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'payment_date',
        'work_days',
        'leave_joint_days',
        'leave_personal_days',
        'leave_entitlement',
        'leave_remaining',
        'basic_salary',
        'total_earnings',
        'total_deductions',
        'tax_amount',
        'net_salary',
        'status'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'work_days' => 'integer',
        'leave_joint_days' => 'integer',
        'leave_personal_days' => 'integer',
        'leave_entitlement' => 'integer',
        'leave_remaining' => 'integer',
        'basic_salary' => 'integer',
        'total_earnings' => 'integer',
        'total_deductions' => 'integer',
        'tax_amount' => 'integer',
        'net_salary' => 'integer',
    ];
    public function details()
    {
        return $this->hasMany(PayslipDetail::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
