<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = [
        'worker_id',
        'month',
        'basic_salary',
        'overtime_hours',
        'deductions',
        'net_salary',
    ];

    public function worker()
    {
        return $this->belongsTo(\App\Models\Worker::class);
    }
}
