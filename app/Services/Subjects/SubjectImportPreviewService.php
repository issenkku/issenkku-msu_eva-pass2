<?php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;

final class SubjectImportPreviewService
{
    private const COMPARED = ['name_th', 'name_en', 'credits', 'lecture_credits', 'lab_credits', 'self_study_credits'];

    public function build(SubjectWorkbookReadResult $read): array
    {
        $existing = Subject::query()
            ->whereIn('code', collect($read->rows)->pluck('code')->all())
            ->get()
            ->keyBy('code');
        $preview = ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []];

        foreach ($read->rows as $row) {
            $current = $existing->get($row->code);
            if ($current === null) {
                $preview['new'][] = ['row' => $row->toArray()];

                continue;
            }

            $incoming = $row->attributes();
            $currentValues = collect(self::COMPARED)->mapWithKeys(
                fn (string $key) => [$key => $current->{$key}],
            )->all();
            $diff = collect(self::COMPARED)->mapWithKeys(function (string $key) use ($currentValues, $incoming) {
                return $currentValues[$key] === $incoming[$key]
                    ? []
                    : [$key => ['old' => $currentValues[$key], 'new' => $incoming[$key]]];
            })->all();
            $item = [
                'row' => $row->toArray(),
                'current' => ['id' => $current->id, ...$currentValues],
                'fingerprint' => self::fingerprint($current),
                'diff' => $diff,
            ];
            $preview[$diff === [] ? 'unchanged' : 'changed'][] = $item;
        }

        $preview['errors'] = array_map(fn ($error) => $error->toArray(), $read->errors);

        return $preview;
    }

    public static function fingerprint(Subject $subject): string
    {
        return hash('sha256', json_encode([
            'id' => $subject->id,
            'code' => $subject->code,
            ...collect(self::COMPARED)->mapWithKeys(fn ($key) => [$key => $subject->{$key}])->all(),
            'is_active' => $subject->is_active,
            'sort_order' => $subject->sort_order,
            'updated_at' => $subject->updated_at?->toJSON(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
