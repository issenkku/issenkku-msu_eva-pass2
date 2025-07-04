<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssignmentData;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotifyEndDate extends Command
{
    protected $signature = 'notify:enddate';
    protected $description = 'Send email to users when end_time is within 7 days';

    public function handle()
    {
        // แจ้งเตือนเฉพาะ assignment ที่ end_time ตรงกับอีก 7 วันข้างหน้า (ก่อนถึง end_time 7 วันพอดี)
        $notifyDate = Carbon::today()->addDays(7);
        $items = AssignmentData::whereDate('end_time', '<=', $notifyDate)->with(['assignments.evaluatorUser'])->get();

        $successCount = 0;
        $failCount = 0;
        $failures = [];
        foreach ($items as $item) {
            foreach ($item->assignments as $assignment) {
                $user = $assignment->evaluatorUser; // หรือ evaluateeUser ตามต้องการ
                if ($user && $user->email) {
                try {
                    $carbonDate = Carbon::parse($item->end_time);
                    $thaiYear = $carbonDate->year + 543;
                    $endDateTh = $carbonDate->format('d/m/') . substr($thaiYear, -2);
                    Mail::raw(
                        "แจ้งเตือนวันสิ้นสุดการประเมิน: กำหนดสิ้นสุดการประเมินคือ ({$endDateTh}) ใกล้ครบวันที่กำหนดแล้ว กรุณาตรวจสอบและดำเนินการประเมินให้เรียบร้อยก่อนถึงกำหนด",
                        function ($message) use ($user, $item, $endDateTh) {
                            $message->to($user->email)
                                ->subject('แจ้งเตือนวันสิ้นสุดการประเมินใกล้ถึงกำหนด');
                        }
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
        $this->info("Notification emails sent: {$successCount}");
        if ($failCount > 0) {
            $this->error("Failed to send: {$failCount}");
            foreach ($failures as $fail) {
                $this->error("Email: {$fail['user']} | Error: {$fail['error']}");
            }
        }
    }
}
