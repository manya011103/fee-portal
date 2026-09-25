<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'registration_date',
        'enrollment_no',
        'name',
        'father_name',
        'mother_name',
        'mobile',
        'father_mobile',
        'mother_mobile',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function feeRecords()
    {
        return $this->hasMany(FeeRecord::class);
    }

    public function getEffectiveDueDate(): Carbon
    {
        return $this->due_date ?? Carbon::parse(config('fee.default_due_date'));
    }

    public function getDaysLate(): int
    {
        $dueDate = $this->getEffectiveDueDate();
        $today = Carbon::today();

        if ($today->lte($dueDate)) {
            return 0;
        }

        return $today->diffInDays($dueDate, true);
    }

    // Naya method: kitni fee abhi bhi baki hai (fine ke bina), saari classes milake
    public function getOutstandingBalance(): float
{
    return $this->feeRecords->sum(function ($record) {
        $totalPayable = (float) $record->total_fee
            - (float) $record->scholarship_fee;

        $paidTotal = (float) $record->payments()->sum('amount');

        return max($totalPayable - $paidTotal, 0);
    });
}

    public function getFineAmount(): int
    {
        // Agar koi fee baki hi nahi hai, toh fine lagne ka sawaal hi nahi
        if ($this->getOutstandingBalance() <= 0) {
            return 0;
        }

        return $this->getDaysLate() * config('fee.fine_per_day');
    }

    public function dueDateHistories()
{
    return $this->hasMany(DueDateHistory::class)->latest();
}
}