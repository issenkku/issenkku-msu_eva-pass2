<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssignmentData;
use Carbon\Carbon;


class UpdateReportStatuses extends Command
{
    protected $signature = 'reports:update-statuses';
    protected $description = 'Update reports to Pending if evaluation end_time has passed';

    public function handle()
    {
        $now = Carbon::now();

        $items = AssignmentData::whereNotNull('end_time')
            ->where('end_time', '<', $now)
            ->with(['assignments.report'])
            ->get();

        $updated = 0;

        foreach ($items as $item) {
            foreach ($item->assignments as $assignment) {
                if (
                    $assignment->report &&
                    in_array($assignment->report->status, ['Draft', 'Assigned'])
                ) {
                    $assignment->report->update(['status' => 'Pending']);
                    $updated++;
                }
            }
        }

        $this->info("Updated {$updated} reports to Pending.");
    }
}
