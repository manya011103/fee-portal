<?php

namespace App\Console\Commands;

use App\Models\FeeReminderQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessFeeReminderQueue extends Command
{
    protected $signature = 'fee:process-reminder-queue';
    protected $description = 'Send up to 1000 queued fee reminders and notify admin via email';

    public function handle(): void
    {
        $batch = FeeReminderQueue::orderBy('id')->limit(1000)->get();

        if ($batch->isEmpty()) {
            $this->info('Queue is empty. Nothing to send.');
            return;
        }

        foreach ($batch as $item) {
            // Abhi sirf LOG — baad mein yahan BSNL API call aayega
            Log::info("SMS would be sent to: {$item->mobile}");

            $item->delete();
        }

        $processedCount = $batch->count();
        $remainingCount = FeeReminderQueue::count();

        $this->info("{$processedCount} reminder(s) processed. {$remainingCount} remaining in queue.");

        // Admin ko summary email bhejo
        $this->sendSummaryEmail($processedCount, $remainingCount);
    }

    private function sendSummaryEmail(int $processed, int $remaining): void
    {
        $adminEmail = env('ADMIN_NOTIFICATION_EMAIL');

        if (! $adminEmail) {
            Log::warning('ADMIN_NOTIFICATION_EMAIL not set — skipping summary email.');
            return;
        }

        $body = "Fee Reminder Batch Processed\n\n"
            . "Processed in this batch: {$processed}\n"
            . "Remaining in queue: {$remaining}\n"
            . "Time: " . now()->format('d M Y, h:i A');

        try {
            Mail::raw($body, function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                    ->subject('Fee Reminder Batch — ' . now()->format('d M Y H:i'));
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send reminder batch summary email', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}