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
        'is_fully_paid',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function recalculateFullyPaid(): void
    {
        $paidTotal = $this->payments()->sum('amount');
        $totalPayable = $this->total_fee - $this->scholarship_fee;

        $this->update(['is_fully_paid' => $paidTotal >= $totalPayable]);
    }
}