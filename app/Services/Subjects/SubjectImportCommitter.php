<?php

namespace App\Services\Subjects;

use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Models\Subject;
use App\Models\User;
use App\Support\AuditLog;
use App\Support\Subjects\SubjectCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SubjectImportCommitter
{
    public function commit(array $snapshot, array $selectedCodes, User $causer): array
    {
        $preview = $snapshot['preview'];
        if ($preview['errors'] !== []) {
            throw new \InvalidArgumentException('Cannot commit a Preview with validation errors.');
        }

        $selected = collect($selectedCodes)->map(fn ($code) => SubjectCode::normalize($code))->unique()->values();
        $allowed = collect($preview['changed'])->pluck('row.code');
        if ($selected->diff($allowed)->isNotEmpty()) {
            throw new \InvalidArgumentException('Selected codes must be changed Preview rows.');
        }

        return DB::transaction(function () use ($snapshot, $preview, $selected, $allowed, $causer): array {
            $allItems = collect($preview['changed'])->concat($preview['unchanged']);
            $allCodes = collect($preview['new'])->pluck('row.code')->concat($allItems->pluck('row.code'))->values();
            $current = Subject::query()->whereIn('code', $allCodes)->lockForUpdate()->get()->keyBy('code');

            foreach ($preview['new'] as $item) {
                if ($current->has($item['row']['code'])) {
                    throw new StaleSubjectImportException;
                }
            }
            foreach ($allItems as $item) {
                $subject = $current->get($item['row']['code']);
                if ($subject === null || SubjectImportPreviewService::fingerprint($subject) !== $item['fingerprint']) {
                    throw new StaleSubjectImportException;
                }
            }

            $created = [];
            $updated = [];
            $sortOrder = (int) (Subject::max('sort_order') ?? 0);
            foreach ($preview['new'] as $item) {
                $row = $item['row'];
                Subject::create([
                    ...Arr::except($row, ['excel_row']),
                    'is_active' => true,
                    'sort_order' => ++$sortOrder,
                ]);
                $created[] = $row['code'];
            }
            foreach ($preview['changed'] as $item) {
                $code = $item['row']['code'];
                if (! $selected->contains($code)) {
                    continue;
                }
                $current[$code]->update(Arr::except($item['row'], ['excel_row', 'code']));
                $updated[] = $code;
            }

            $result = [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $allowed->diff($selected)->values()->all(),
                'unchanged' => collect($preview['unchanged'])->pluck('row.code')->values()->all(),
            ];
            AuditLog::record('จัดการรายวิชา', 'นำเข้าข้อมูลรายวิชา', [
                'filename' => basename($snapshot['filename']),
                'preview_token_hash' => $snapshot['token_hash'],
                'created_count' => count($result['created']), 'created_codes' => $result['created'],
                'updated_count' => count($result['updated']), 'updated_codes' => $result['updated'],
                'skipped_count' => count($result['skipped']), 'skipped_codes' => $result['skipped'],
                'unchanged_count' => count($result['unchanged']), 'unchanged_codes' => $result['unchanged'],
            ], causer: $causer);

            return $result;
        }, 3);
    }
}
