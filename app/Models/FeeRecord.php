<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_name',
        'total_fee',
        'scholarship_fee',
        'fee1_paid_date',
        'fee1_paid_amt',
        'fee2_paid_date',
        'fee2_paid_amt',
        'fee3_paid_date',
        'fee3_paid_amt',
        'fee4_paid_date',
        'fee4_paid_amt',
        'fee5_paid_date',
        'fee5_paid_amt',
        'fee6_paid_date',
        'fee6_paid_amt',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payments()
{
    return $this->hasMany(FeePayment::class);
}

    
}