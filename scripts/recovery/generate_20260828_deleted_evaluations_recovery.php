<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['source-db::', 'output::']);
$sourceDatabase = $options['source-db'] ?? 'msu_eva_recovery_analysis_20260828';
$outputPath = $options['output'] ?? dirname(__DIR__, 2).'/storage/app/recovery/restore_deleted_academic_manager_20260828.sql';

if (! preg_match('/^[A-Za-z0-9_]+$/', $sourceDatabase)) {
    throw new InvalidArgumentException('Invalid source database name.');
}

$mapping = [
    290 => ['new_report_id' => 391, 'evaluatee_id' => 81, 'assignment_data_id' => 42, 'report_data_id' => 41],
    297 => ['new_report_id' => 384, 'evaluatee_id' => 89, 'assignment_data_id' => 43, 'report_data_id' => 36],
    301 => ['new_report_id' => 389, 'evaluatee_id' => 122, 'assignment_data_id' => 43, 'report_data_id' => 36],
    303 => ['new_report_id' => 382, 'evaluatee_id' => 103, 'assignment_data_id' => 44, 'report_data_id' => 36],
    305 => ['new_report_id' => 377, 'evaluatee_id' => 70, 'assignment_data_id' => 45, 'report_data_id' => 36],
    307 => ['new_report_id' => 379, 'evaluatee_id' => 80, 'assignment_data_id' => 45, 'report_data_id' => 36],
    308 => ['new_report_id' => 380, 'evaluatee_id' => 90, 'assignment_data_id' => 45, 'report_data_id' => 36],
    310 => ['new_report_id' => 375, 'evaluatee_id' => 96, 'assignment_data_id' => 46, 'report_data_id' => 36],
    311 => ['new_report_id' => 376, 'evaluatee_id' => 121, 'assignment_data_id' => 46, 'report_data_id' => 36],
    313 => ['new_report_id' => 371, 'evaluatee_id' => 74, 'assignment_data_id' => 47, 'report_data_id' => 36],
    314 => ['new_report_id' => 372, 'evaluatee_id' => 76, 'assignment_data_id' => 47, 'report_data_id' => 36],
    316 => ['new_report_id' => 368, 'evaluatee_id' => 93, 'assignment_data_id' => 48, 'report_data_id' => 36],
    318 => ['new_report_id' => 367, 'evaluatee_id' => 77, 'assignment_data_id' => 49, 'report_data_id' => 36],
    320 => ['new_report_id' => 365, 'evaluatee_id' => 115, 'assignment_data_id' => 51, 'report_data_id' => 36],
    321 => ['new_report_id' => 362, 'evaluatee_id' => 112, 'assignment_data_id' => 52, 'report_data_id' => 36],
    324 => ['new_report_id' => 363, 'evaluatee_id' => 114, 'assignment_data_id' => 52, 'report_data_id' => 36],
];

$config = config('database.connections.mysql');
$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $sourceDatabase,
    ),
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

/** @return string SQL literal */
function sqlValue(PDO $pdo, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return $pdo->quote((string) $value);
}

function sqlTimestamp(?string $value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }

    return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

/** @param array<string, mixed> $value */
function canonicalJson(array $value): string
{
    $sort = function (&$item) use (&$sort): void {
        if (! is_array($item)) {
            return;
        }

        foreach ($item as &$child) {
            $sort($child);
        }

        if (! array_is_list($item)) {
            ksort($item);
        }
    };

    $sort($value);

    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
}

/** @return array<int, array<string, mixed>> */
function rows(PDO $pdo, string $sql, array $bindings = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($bindings);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

$oldReportIds = array_keys($mapping);
$newReportIds = array_column($mapping, 'new_report_id');
$oldReportList = implode(',', $oldReportIds);
$newReportList = implode(',', $newReportIds);

$reportStates = [];
foreach ($mapping as $oldReportId => $item) {
    $reportStates[$oldReportId] = [
        ...$item,
        'old_report_id' => $oldReportId,
        'status' => null,
        'quantity_scores' => [],
        'quality_scores' => [],
        'direct_evidence' => [],
        'workload_evidence' => [],
        'last_activity_at' => null,
    ];
}

$reportActivities = rows(
    $pdo,
    "SELECT id, subject_id, properties, created_at
       FROM activity_log
      WHERE subject_type LIKE '%Reports'
        AND subject_id IN ({$oldReportList})
      ORDER BY id",
);

foreach ($reportActivities as $activity) {
    $oldReportId = (int) $activity['subject_id'];
    $properties = json_decode($activity['properties'], true, flags: JSON_THROW_ON_ERROR);
    $state = &$reportStates[$oldReportId];

    if (isset($properties['อัพเดตสถานะรายงาน'])) {
        $state['status'] = $properties['อัพเดตสถานะรายงาน'];
    }

    if (isset($properties['อัพเดตคะแนนเชิงปริมาณ'])) {
        $state['quantity_scores'] = $properties['อัพเดตคะแนนเชิงปริมาณ'];
    }

    if (isset($properties['อัพเดตคะแนนเชิงคุณภาพ'])) {
        $state['quality_scores'] = $properties['อัพเดตคะแนนเชิงคุณภาพ'];
    }

    if (array_key_exists('อัพเดตหลักฐาน', $properties)) {
        $state['direct_evidence'] = $properties['อัพเดตหลักฐาน'];
    }

    if (isset($properties['หลักฐานก่อนหน้า'])) {
        $state['workload_evidence'] = array_values(array_filter(
            $properties['หลักฐานก่อนหน้า'],
            fn (array $evidence): bool => ! empty($evidence['workload_entry_id']),
        ));
    }

    $state['last_activity_at'] = $activity['created_at'];
    unset($state);
}

foreach ($reportStates as $oldReportId => $state) {
    if ($state['status'] === null) {
        throw new RuntimeException("No recoverable report activity found for report {$oldReportId}.");
    }
}

$workloadStates = [];
$workloadActivities = rows(
    $pdo,
    "SELECT id, subject_id, event, properties, created_at
       FROM activity_log
      WHERE subject_type LIKE '%WorkloadEntry'
      ORDER BY id",
);

foreach ($workloadActivities as $activity) {
    $entryId = (int) $activity['subject_id'];
    $properties = json_decode($activity['properties'], true, flags: JSON_THROW_ON_ERROR);

    if ($activity['event'] === 'deleted') {
        $workloadStates[$entryId]['deleted'] = true;
        continue;
    }

    $attributes = $properties['attributes'] ?? [];
    $workloadStates[$entryId] ??= [
        'id' => $entryId,
        'created_at' => $activity['created_at'],
        'updated_at' => $activity['created_at'],
        'deleted' => false,
    ];
    $workloadStates[$entryId] = array_merge($workloadStates[$entryId], $attributes);
    $workloadStates[$entryId]['updated_at'] = $activity['created_at'];
}

$workloadsByOldReport = array_fill_keys($oldReportIds, []);
foreach ($workloadStates as $entryId => $state) {
    if (($state['deleted'] ?? false) || ! isset($state['report_id'])) {
        continue;
    }

    $oldReportId = (int) $state['report_id'];
    if (isset($workloadsByOldReport[$oldReportId])) {
        $workloadsByOldReport[$oldReportId][$entryId] = $state;
    }
}

$validWorkloadFormIds = array_fill_keys(array_map(
    'intval',
    array_column(rows($pdo, 'SELECT id FROM workload_forms'), 'id'),
), true);
$validSubjectIds = array_fill_keys(array_map(
    'intval',
    array_column(rows($pdo, 'SELECT id FROM subjects'), 'id'),
), true);

$workloadTargetIds = [];
$workloadsToInsert = [];
$skippedWorkloadsMissingForm = 0;
$skippedWorkloadsMissingSubject = 0;

foreach ($workloadsByOldReport as $oldReportId => $entries) {
    $newReportId = $mapping[$oldReportId]['new_report_id'];

    foreach ($entries as $oldEntryId => $entry) {
        $workloadFormId = (int) $entry['workload_form_id'];
        $subjectId = isset($entry['subject_id']) ? (int) $entry['subject_id'] : null;

        if (! isset($validWorkloadFormIds[$workloadFormId])) {
            $skippedWorkloadsMissingForm++;
            continue;
        }

        if ($subjectId !== null && ! isset($validSubjectIds[$subjectId])) {
            $skippedWorkloadsMissingSubject++;
            continue;
        }

        $fieldValues = is_array($entry['field_values'])
            ? $entry['field_values']
            : json_decode((string) $entry['field_values'], true, flags: JSON_THROW_ON_ERROR);
        $canonicalFields = canonicalJson($fieldValues);

        $workloadTargetIds[$oldEntryId] = $oldEntryId;
        $workloadsToInsert[$oldEntryId] = [
            'id' => $oldEntryId,
            'field_values' => $canonicalFields,
            'calculated_score' => $entry['calculated_score'] ?? null,
            'report_id' => $newReportId,
            'workload_form_id' => $workloadFormId,
            'subject_id' => $subjectId,
            'created_at' => sqlTimestamp($entry['created_at'] ?? null),
            'updated_at' => sqlTimestamp($entry['updated_at'] ?? null),
        ];
    }
}

$missingWorkloadEvidence = 0;
$workloadEvidence = [];
$directEvidence = [];
$quantityScores = [];
$qualityScores = [];

foreach ($reportStates as $oldReportId => $state) {
    $newReportId = $state['new_report_id'];
    $timestamp = sqlTimestamp($state['last_activity_at']);

    foreach ($state['quantity_scores'] as $score) {
        $scoreC = $score['scoreC'] ?? null;
        $description = isset($score['description']) ? trim((string) $score['description']) : null;
        if ($scoreC === null && ($description === null || $description === '')) {
            continue;
        }

        $quantityScores[] = [
            'quantity_sub_criteria_id' => (int) $score['subCriteriaId'],
            'report_id' => $newReportId,
            'score_C' => $scoreC,
            'score_D' => $score['scoreD'] ?? null,
            'description' => $description !== '' ? $description : null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    foreach ($state['quality_scores'] as $score) {
        $qualityScores[] = [
            'quality_sub_criteria_id' => (int) $score['subCriteriaId'],
            'report_id' => $newReportId,
            'score' => $score['score'] ?? null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    foreach ($state['direct_evidence'] as $evidence) {
        if (empty($evidence['link'])) {
            continue;
        }

        $directEvidence[] = [
            'evaluation_list_id' => (int) $evidence['evaluation_list_id'],
            'quality_main_criteria_id' => isset($evidence['quality_main_criteria_id']) ? (int) $evidence['quality_main_criteria_id'] : null,
            'report_id' => $newReportId,
            'link' => $evidence['link'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    foreach ($state['workload_evidence'] as $evidence) {
        $oldEntryId = (int) $evidence['workload_entry_id'];
        if (! isset($workloadTargetIds[$oldEntryId])) {
            $missingWorkloadEvidence++;
            continue;
        }

        $workloadEvidence[] = [
            'evaluation_list_id' => (int) $evidence['evaluation_list_id'],
            'quality_main_criteria_id' => isset($evidence['quality_main_criteria_id']) ? (int) $evidence['quality_main_criteria_id'] : null,
            'report_id' => $newReportId,
            'workload_entry_id' => $workloadTargetIds[$oldEntryId],
            'link' => $evidence['link'],
            'created_at' => sqlTimestamp($evidence['created_at'] ?? $timestamp),
            'updated_at' => sqlTimestamp($evidence['updated_at'] ?? $timestamp),
        ];
    }
}

$sql = [];
$sql[] = '-- Recovery patch generated from msu_eva_f2.sql activity_log';
$sql[] = '-- Scope: 15 academic evaluations and 1 management evaluation deleted on 2026-08-28';
$sql[] = '-- Run only against the same production database lineage after taking a fresh full backup.';
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = 'SET @recovery_started_at = NOW();';
$sql[] = '';
$sql[] = '-- Durable before-images. Existing tables deliberately abort a second run.';
foreach (['reports', 'assignments', 'quantity_scores', 'quality_scores', 'evidence_answers', 'workload_entries'] as $table) {
    $where = match ($table) {
        'reports' => "id IN ({$newReportList})",
        'assignments' => "report_id IN ({$newReportList})",
        default => "report_id IN ({$newReportList})",
    };
    $sql[] = "CREATE TABLE recovery_20260828_before_restore_{$table} AS SELECT * FROM {$table} WHERE {$where};";
}
$sql[] = '';
$sql[] = 'START TRANSACTION;';
$sql[] = '';
$sql[] = '-- Preconditions: abort when assignment/report mapping or reusable workload IDs are unsafe.';
$sql[] = 'CREATE TEMPORARY TABLE recovery_guard (ok TINYINT NOT NULL CHECK (ok = 1));';
foreach ($mapping as $oldReportId => $item) {
    $sql[] = sprintf(
        "INSERT INTO recovery_guard (ok) SELECT (COUNT(*) = 1) FROM assignments a JOIN reports r ON r.id = a.report_id WHERE a.assignment_data_id = %d AND a.report_id = %d AND a.evaluatee_id = %d AND r.report_data_id = %d;",
        $item['assignment_data_id'],
        $item['new_report_id'],
        $item['evaluatee_id'],
        $item['report_data_id'],
    );
}
foreach ($workloadsToInsert as $entry) {
    $sql[] = sprintf(
        'INSERT INTO recovery_guard (ok) SELECT (COUNT(*) = 0) FROM workload_entries WHERE id = %d;',
        $entry['id'],
    );
}
$sql[] = '';
$sql[] = '-- The user confirmed these newly-created reports have no new input.';
$sql[] = '-- Clear their child rows, then restore the complete recoverable snapshot.';
$sql[] = "DELETE FROM evidence_answers WHERE report_id IN ({$newReportList});";
$sql[] = "DELETE FROM quantity_scores WHERE report_id IN ({$newReportList});";
$sql[] = "DELETE FROM quality_scores WHERE report_id IN ({$newReportList});";
$sql[] = "DELETE FROM workload_entries WHERE report_id IN ({$newReportList});";
$sql[] = '';
$sql[] = '-- Restore report progress captured immediately before deletion.';
foreach ($reportStates as $state) {
    $sql[] = sprintf(
        'UPDATE reports SET status = %s WHERE id = %d;',
        sqlValue($pdo, $state['status']),
        $state['new_report_id'],
    );
}
$sql[] = '';

if ($workloadsToInsert !== []) {
    $sql[] = 'INSERT INTO workload_entries (id, field_values, calculated_score, report_id, workload_form_id, subject_id, created_at, updated_at) VALUES';
    $values = [];
    foreach ($workloadsToInsert as $entry) {
        $values[] = sprintf(
            '(%s, %s, %s, %s, %s, %s, %s, %s)',
            sqlValue($pdo, $entry['id']),
            sqlValue($pdo, $entry['field_values']),
            sqlValue($pdo, $entry['calculated_score']),
            sqlValue($pdo, $entry['report_id']),
            sqlValue($pdo, $entry['workload_form_id']),
            sqlValue($pdo, $entry['subject_id']),
            sqlValue($pdo, $entry['created_at']),
            sqlValue($pdo, $entry['updated_at']),
        );
    }
    $sql[] = implode(",\n", $values).';';
    $sql[] = '';
}

foreach ($quantityScores as $score) {
    $sql[] = sprintf(
        'INSERT INTO quantity_scores (quantity_sub_criteria_id, report_id, score_C, score_D, description, modifier_user_id, modifier_role, created_at, updated_at) SELECT %s, %s, %s, %s, %s, NULL, NULL, %s, %s WHERE NOT EXISTS (SELECT 1 FROM quantity_scores WHERE report_id = %s AND quantity_sub_criteria_id = %s);',
        sqlValue($pdo, $score['quantity_sub_criteria_id']),
        sqlValue($pdo, $score['report_id']),
        sqlValue($pdo, $score['score_C']),
        sqlValue($pdo, $score['score_D']),
        sqlValue($pdo, $score['description']),
        sqlValue($pdo, $score['created_at']),
        sqlValue($pdo, $score['updated_at']),
        sqlValue($pdo, $score['report_id']),
        sqlValue($pdo, $score['quantity_sub_criteria_id']),
    );
}
$sql[] = '';

foreach ($qualityScores as $score) {
    $sql[] = sprintf(
        'INSERT INTO quality_scores (user_id, quality_sub_criteria_id, report_id, score, created_at, updated_at) SELECT NULL, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM quality_scores WHERE report_id = %s AND quality_sub_criteria_id = %s);',
        sqlValue($pdo, $score['quality_sub_criteria_id']),
        sqlValue($pdo, $score['report_id']),
        sqlValue($pdo, $score['score']),
        sqlValue($pdo, $score['created_at']),
        sqlValue($pdo, $score['updated_at']),
        sqlValue($pdo, $score['report_id']),
        sqlValue($pdo, $score['quality_sub_criteria_id']),
    );
}
$sql[] = '';

foreach ([...$directEvidence, ...$workloadEvidence] as $evidence) {
    $workloadEntryId = $evidence['workload_entry_id'] ?? null;
    $sql[] = sprintf(
        'INSERT INTO evidence_answers (evaluation_list_id, quality_main_criteria_id, support_criteria_id, support_activity_entry_id, report_id, workload_entry_id, link, created_at, updated_at) SELECT %s, %s, NULL, NULL, %s, %s, %s, %s, %s WHERE NOT EXISTS (SELECT 1 FROM evidence_answers WHERE report_id = %s AND evaluation_list_id = %s AND quality_main_criteria_id <=> %s AND workload_entry_id <=> %s AND link <=> %s)%s;',
        sqlValue($pdo, $evidence['evaluation_list_id']),
        sqlValue($pdo, $evidence['quality_main_criteria_id']),
        sqlValue($pdo, $evidence['report_id']),
        sqlValue($pdo, $workloadEntryId),
        sqlValue($pdo, $evidence['link']),
        sqlValue($pdo, $evidence['created_at']),
        sqlValue($pdo, $evidence['updated_at']),
        sqlValue($pdo, $evidence['report_id']),
        sqlValue($pdo, $evidence['evaluation_list_id']),
        sqlValue($pdo, $evidence['quality_main_criteria_id']),
        sqlValue($pdo, $workloadEntryId),
        sqlValue($pdo, $evidence['link']),
        $workloadEntryId === null
            ? ''
            : sprintf(' AND EXISTS (SELECT 1 FROM workload_entries WHERE id = %d AND report_id = %d)', $workloadEntryId, $evidence['report_id']),
    );
}
$sql[] = '';
$sql[] = 'COMMIT;';
$sql[] = '';
$sql[] = '-- Verification summary';
$sql[] = "SELECT a.evaluatee_id, u.employee_id, u.name, a.report_id, r.status, COUNT(DISTINCT qs.id) quantity_scores, COUNT(DISTINCT qls.id) quality_scores, COUNT(DISTINCT we.id) workload_entries FROM assignments a JOIN users u ON u.id = a.evaluatee_id JOIN reports r ON r.id = a.report_id LEFT JOIN quantity_scores qs ON qs.report_id = r.id LEFT JOIN quality_scores qls ON qls.report_id = r.id LEFT JOIN workload_entries we ON we.report_id = r.id WHERE a.report_id IN ({$newReportList}) GROUP BY a.evaluatee_id, u.employee_id, u.name, a.report_id, r.status ORDER BY a.report_id;";

$outputDirectory = dirname($outputPath);
if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0777, true) && ! is_dir($outputDirectory)) {
    throw new RuntimeException("Cannot create output directory: {$outputDirectory}");
}

file_put_contents($outputPath, implode(PHP_EOL, $sql).PHP_EOL);

$summary = [
    'reports' => count($reportStates),
    'quantity_scores' => count($quantityScores),
    'quality_scores' => count($qualityScores),
    'workload_entries_to_insert' => count($workloadsToInsert),
    'workload_entries_skipped_missing_form' => $skippedWorkloadsMissingForm,
    'workload_entries_skipped_missing_subject' => $skippedWorkloadsMissingSubject,
    'direct_evidence' => count($directEvidence),
    'workload_evidence' => count($workloadEvidence),
    'unrecoverable_workload_evidence_rows' => $missingWorkloadEvidence,
    'output' => realpath($outputPath) ?: $outputPath,
];

echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
