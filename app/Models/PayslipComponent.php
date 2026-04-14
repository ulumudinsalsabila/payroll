<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipComponent extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name', 
        'type', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function details()
    {
        return $this->hasMany(PayslipDetail::class);
    }
}
