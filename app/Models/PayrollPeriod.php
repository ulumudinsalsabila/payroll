<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'month', 
        'year', 
        'description', 
        'status', 
        'is_leave_distributed', 
        'published_at'
    ];

    protected $casts = [
        'is_leave_distributed' => 'boolean',
        'published_at' => 'datetime',
    ];
    
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
