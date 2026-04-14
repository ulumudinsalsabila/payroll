<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerRate extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';
    
    protected $fillable = [
        'category', 
        'min_bruto', 
        'max_bruto', 
        'percentage'
    ];

    protected $casts = [
        'min_bruto' => 'integer',
        'max_bruto' => 'integer',
        'percentage' => 'decimal:2',
    ];
}
