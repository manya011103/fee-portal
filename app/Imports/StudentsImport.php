<?php

namespace App\Imports;

use App\Models\FeePayment;
use App\Models\FeeRecord;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public array $skipped = [];
    public int $successCount = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $enrollmentNo = trim($row['st_enrol'] ?? '');
            $mobile = trim($row['st_mobile'] ?? '');
            $className = strtoupper(trim($row['st_classname'] ?? ''));

            if (! $enrollmentNo || ! $mobile || ! $className) {
                $this->errors[] = "Row skipped: Enrollment No, Mobile, or Class is missing.";
                continue;
            }

            $student = Student::where('enrollment_no', $enrollmentNo)->first();

            if (! $student) {
                $mobileTaken = Student::where('mobile', $mobile)->exists();

                if ($mobileTaken) {
                    $this->errors[] = "Skipped: {$enrollmentNo} ({$row['st_name']}) — mobile {$mobile} is already used by another student.";
                    continue;
                }

                $student = Student::create([
                    'registration_date' => $this->excelDate($row['st_doreg'] ?? null),
                    'enrollment_no' => $enrollmentNo,
                    'name' => trim($row['st_name'] ?? ''),
                    'father_name' => trim($row['st_fathername'] ?? ''),
                    'mother_name' => trim($row['st_mothername'] ?? ''),
                    'mobile' => $mobile,
                    'father_mobile' => $row['st_fathermobile'] ?? null,
                    'mother_mobile' => $row['st_mothermobile'] ?? null,
                ]);
            }

            // Already imported? Skip it, don't touch existing data
            $existingFeeRecord = FeeRecord::where('student_id', $student->id)
                ->where('class_name', $className)
                ->first();

            if ($existingFeeRecord) {
                $this->skipped[] = "{$student->name} ({$enrollmentNo}) — {$className} already imported.";
                continue;
            }

            // New fee record — create it
            $feeRecord = FeeRecord::create([
                'student_id' => $student->id,
                'class_name' => $className,
                'total_fee' => $row['st_totalfee'] ?? 0,
                'scholarship_fee' => $row['st_scholarshipfee'] ?? 0,
            ]);

            // Create payments from the 6 Excel columns
            for ($i = 1; $i <= 6; $i++) {
                $amt = $row["st_depositedfee{$i}paidamt"] ?? null;
                $date = $row["st_depositedfee{$i}paiddate"] ?? null;

                if ($amt) {
                    FeePayment::create([
                        'fee_record_id' => $feeRecord->id,
                        'amount' => $amt,
                        'payment_date' => $this->excelDate($date) ?? now()->format('Y-m-d'),
                        'payment_mode' => 'accounts',
                    ]);
                }
            }

            $feeRecord->recalculateFullyPaid(); 

            $this->successCount++;
        }
    }

    private function excelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}