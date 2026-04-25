<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'fingerprint_id',
        'employee_id',
        'employee_name',
        'period_month',
        'present_days',
        'absent_days',
        'late_minutes',
        'overtime_hours',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
