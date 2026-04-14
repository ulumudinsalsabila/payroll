<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipDetail extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payslip_id', 
        'payslip_component_id', 
        'amount'
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    public function component()
    {
        return $this->belongsTo(PayslipComponent::class, 'payslip_component_id');
    }
}
