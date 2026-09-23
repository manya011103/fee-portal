<?php

namespace App\Console\Commands;

use App\Models\FeeReminderQueue;
use App\Models\Student;
use Illuminate\Console\Command;

class BuildFeeReminderQueue extends Command
{
    protected $signature = 'fee:build-reminder-queue';
    protected $description = 'Build today\'s fee reminder queue (mobile numbers only)';

    private array $stageDays = [30, 20, 10, 7, 5, 3, 1, 0];

    public function handle(): void
    {
        // Purani queue clear karo, fresh banao
        FeeReminderQueue::truncate();

        $students = Student::with('feeRecords.payments')->get();
        $count = 0;

        foreach ($students as $student) {
            if ($student->getOutstandingBalance() <= 0) {
                continue;
            }

            $dueDate = $student->getEffectiveDueDate();
            $daysUntilDue = (int) now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);

            if (! in_array($daysUntilDue, $this->stageDays, true)) {
                continue;
            }

            FeeReminderQueue::create(['mobile' => $student->mobile]);
            $count++;
        }

        $this->info("{$count} mobile number(s) added to reminder queue.");
    }
}