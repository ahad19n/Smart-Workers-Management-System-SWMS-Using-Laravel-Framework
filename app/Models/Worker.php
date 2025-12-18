<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model {
    use HasFactory;

    protected $fillable = ['user_id','employee_code','position','join_date','skills','status'];
    protected $casts = [
        'join_date' => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function attendance() {
        return $this->hasMany(Attendance::class);
    }
}
