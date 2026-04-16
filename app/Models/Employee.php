<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'position',
        'department',
        'address',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'join_date',
        'leave_balance',
        'npwp_number',
        'ptkp_status',
        'ter_category'
    ];

    protected $casts = [
        'join_date' => 'date',
        'leave_balance' => 'integer',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
    public function leaveTransactions()
    {
        return $this->hasMany(LeaveTransaction::class);
    }
}
