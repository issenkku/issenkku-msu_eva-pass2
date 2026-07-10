<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\WorkloadEntry;
use Illuminate\Support\Facades\DB;

class PreviousWorkloadImportService
{
    public function importForReport(Reports $currentReport, int $evaluateeId, ?int $sourceReportId = null): array
    {
        $previousReport = $sourceReportId
            ? $this->candidatePreviousReports($currentReport, $evaluateeId)->firstWhere('id', $sourceReportId)
            : $this->findPreviousReport($currentReport, $evaluateeId);

        if (! $previousReport) {
            return [
                'source_report_id' => null,
                'copied_entries' => 0,
                'copied_evidence' => 0,
            ];
        }

        return DB::transaction(function () use ($currentReport, $previousReport) {
            $existingFingerprints = WorkloadEntry::where('report_id', $currentReport->id)
                ->get()
                ->map(fn (WorkloadEntry $entry) => $this->fingerprint($entry))
                ->flip();

            $copiedEntries = 0;
            $copiedEvidence = 0;

            $previousEntries = WorkloadEntry::where('report_id', $previousReport->id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            foreach ($previousEntries as $previousEntry) {
                $fingerprint = $this->fingerprint($previousEntry);

                if ($existingFingerprints->has($fingerprint)) {
                    continue;
                }

                $newEntry = WorkloadEntry::create([
                    'field_values' => $previousEntry->field_values ?? [],
                    'calculated_score' => $previousEntry->calculated_score,
                    'report_id' => $currentReport->id,
                    'workload_form_id' => $previousEntry->workload_form_id,
                    'subject_id' => $previousEntry->subject_id,
                ]);

                $existingFingerprints->put($fingerprint, true);
                $copiedEntries++;
                $copiedEvidence += $this->copyEvidenceForEntry($previousEntry, $newEntry);
            }

            return [
                'source_report_id' => $previousReport->id,
                'copied_entries' => $copiedEntries,
                'copied_evidence' => $copiedEvidence,
            ];
        });
    }

    public function candidatePreviousReports(Reports $currentReport, int $evaluateeId)
    {
        $currentAssignment = Assignments::with('assignmentData')
            ->where('report_id', $currentReport->id)
            ->where('evaluatee_id', $evaluateeId)
            ->first();

        if (! $currentAssignment) {
            return collect();
        }

        return Assignments::with(['assignmentData', 'report'])
            ->where('evaluatee_id', $evaluateeId)
            ->where('report_id', '!=', $currentReport->id)
            ->whereHas('report', function ($query) use ($currentReport) {
                $query->where('report_data_id', $currentReport->report_data_id);
            })
            ->get()
            ->filter(function (Assignments $assignment) use ($currentAssignment) {
                if (! $assignment->report || ! $assignment->assignmentData) {
                    return false;
                }

                if (! $currentAssignment->assignmentData?->start_time) {
                    return true;
                }

                return $assignment->assignmentData->end_time
                    && $assignment->assignmentData->end_time->lt($currentAssignment->assignmentData->start_time);
            })
            ->sortByDesc(fn (Assignments $assignment) => $assignment->assignmentData?->end_time?->timestamp ?? 0)
            ->map(fn (Assignments $assignment) => $assignment->report->setRelation('assignments', $assignment))
            ->values();
    }

    private function findPreviousReport(Reports $currentReport, int $evaluateeId): ?Reports
    {
        return $this->candidatePreviousReports($currentReport, $evaluateeId)->first();
    }

    private function copyEvidenceForEntry(WorkloadEntry $previousEntry, WorkloadEntry $newEntry): int
    {
        $count = 0;

        EvidenceAnswer::where('workload_entry_id', $previousEntry->id)
            ->where('report_id', $previousEntry->report_id)
            ->orderBy('created_at')
            ->get()
            ->each(function (EvidenceAnswer $answer) use ($newEntry, &$count) {
                if (trim((string) $answer->link) === '') {
                    return;
                }

                EvidenceAnswer::create([
                    'evaluation_list_id' => $answer->evaluation_list_id,
                    'quality_main_criteria_id' => $answer->quality_main_criteria_id,
                    'report_id' => $newEntry->report_id,
                    'workload_entry_id' => $newEntry->id,
                    'link' => $answer->link,
                ]);

                $count++;
            });

        return $count;
    }

    private function fingerprint(WorkloadEntry $entry): string
    {
        $fieldValues = $this->sortRecursively((array) ($entry->field_values ?? []));

        return json_encode([
            'workload_form_id' => (int) $entry->workload_form_id,
            'subject_id' => $entry->subject_id ? (int) $entry->subject_id : null,
            'field_values' => $fieldValues,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function sortRecursively(array $value): array
    {
        ksort($value);

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->sortRecursively($item);
            }
        }

        return $value;
    }
}
