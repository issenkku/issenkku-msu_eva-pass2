<?php

namespace App\Console\Commands;

use App\Models\AssignmentData;
use App\Models\Setting\Settings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Mail\NotifyEndDateMail;
use Illuminate\Support\Facades\Mail;

class NotifyEndDate extends Command
{
    protected $signature = 'notify:enddate';

    protected $description = 'Send email to users when end_time is within configured days';

    public function handle()
    {
        // ดึงค่าจำนวนวันแจ้งเตือนจาก Settings
        $settings = Settings::first();
        $notificationDays = $settings ? $settings->notification_days : 7; // ค่าเริ่มต้น 7 วัน

        // แจ้งเตือน assignment ที่ end_time ใกล้ครบตามจำนวนวันที่กำหนด
        $today = Carbon::today();
        $items = AssignmentData::where('end_time', '>=', $today)
            ->where('end_time', '<=', $today->copy()->addDays($notificationDays))
            ->with([
                'assignments.evaluateeUser',
                'evaluatorPosition.user',
            ])
            ->get();

        $successCount = 0;
        $failCount = 0;
        $failures = [];

        foreach ($items as $item) {
            foreach ($item->assignments as $assignment) {
                // เช็คว่ามี report และสถานะเป็น Completed หรือไม่
                if (isset($assignment->report) && $assignment->report && $assignment->report->status === 'Completed') {
                    continue; // ข้ามถ้าเสร็จแล้ว
                }
                // ส่งให้ทั้ง evaluator และ evaluatee (ถ้ามีอีเมล)
                $recipients = [];
                if (isset($assignment->evaluatorUser) && $assignment->evaluatorUser && $assignment->evaluatorUser->email) {
                    $recipients[] = $assignment->evaluatorUser;
                }
                if (isset($assignment->evaluateeUser) && $assignment->evaluateeUser && $assignment->evaluateeUser->email) {
                    $recipients[] = $assignment->evaluateeUser;
                }
                foreach ($recipients as $user) {
                    try {
                        $carbonDate = Carbon::parse($item->end_time);
                        $thaiYear = $carbonDate->year + 543;
                        $endDateTh = $carbonDate->format('d/m/').substr($thaiYear, -2);

                        // คำนวณจำนวนวันที่เหลือ
                        $daysLeft = $today->diffInDays($carbonDate, false);
                        $daysLeftText = $daysLeft == 0 ? 'วันนี้' : "อีก {$daysLeft} วัน";

                        Mail::to($user->email)->send(
                            new NotifyEndDateMail(
                                $user->name,
                                $endDateTh,
                                $daysLeftText
                            )
                        );
                        $successCount++;
                    } catch (\Exception $e) {
                        $failCount++;
                        $failures[] = [
                            'user' => $user->email,
                            'error' => $e->getMessage(),
                        ];
                        \Log::error('[NotifyEndDate] Failed to send email', [
                            'user' => $user->email,
                            'assignment_id' => $item->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
        }

        $this->info("Notification emails sent: {$successCount} (using {$notificationDays} days setting)");
        if ($failCount > 0) {
            $this->error("Failed to send: {$failCount}");
            foreach ($failures as $fail) {
                $this->error("Email: {$fail['user']} | Error: {$fail['error']}");
            }
        }
    }
}
