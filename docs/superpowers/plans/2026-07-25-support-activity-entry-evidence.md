# Support Activity Entry Evidence Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Store and edit support evidence links on individual activity/project entries while preserving criterion-level evidence for criteria that do not allow activity entries.

**Architecture:** Add a nullable activity-entry foreign key to the existing `evidence_answers` table and expose evidence through `SupportActivityEntry`. `SupportScoreService` continues to own criterion scores and criterion-level evidence, while `SupportActivityEntryService` atomically owns activity entries and their nested evidence. The shared read model supplies the same nested shape to editable views, read-only views, and exports.

**Tech Stack:** PHP 8.2, Laravel 11, Eloquent, Blade, vanilla ES modules, Node test runner, Pest/PHPUnit, Vite.

## Global Constraints

- Apply per-entry evidence only when `support_criterias.allow_activity_entries = true`.
- Delete existing criterion-level support evidence for activity-enabled criteria during migration.
- Preserve criterion-level evidence for criteria where activity entries are disabled.
- Reuse `evidence_answers`; do not add a second evidence table or a JSON evidence column.
- An activity-enabled required-evidence criterion needs at least one valid URL across all its activity entries, not one URL per entry.
- Evidence remains URL-only and accepts the existing `http` and `https` schemes.
- Deleting an activity entry must cascade-delete its evidence.
- Do not change support score, weight, or achievement-score calculations.
- Do not change quantity or quality evidence behavior.

---

## File Map

- `database/migrations/2026_07_25_000002_add_support_activity_entry_to_evidence_answers.php`: add the nullable foreign key and remove obsolete development data.
- `app/Models/EvidenceAnswer.php`: make the foreign key assignable and expose its activity entry.
- `app/Models/SupportActivityEntry.php`: expose ordered evidence rows.
- `app/Support/SupportScoreRules.php`: validate nested activity evidence URLs.
- `app/Services/SupportScoreService.php`: normalize nested evidence, enforce the aggregate required-evidence rule, and leave activity evidence to the activity service.
- `app/Services/SupportActivityEntryService.php`: persist each activity entry and replace only that entry's evidence rows.
- `app/Support/SupportCriteriaReadModel.php`: return `evidence_links` on each activity entry and criterion links only for non-activity criteria.
- `resources/views/components/support-activity-entry-editor.blade.php`: render evidence controls/read-only links at the end of grouped activity cards.
- `resources/views/components/support-criteria-table.blade.php`: render the same nested evidence for ungrouped cards and retain criterion evidence only where appropriate.
- `resources/js/support-activity-entries.js`: produce stable nested field names for activity evidence.
- `resources/views/components/support-criteria-table-script.blade.php`: create, reindex, validate, snapshot, and restore nested evidence controls.
- `app/Exports/SingleReportExport.php`: emit each activity's evidence immediately after that activity.
- Focused tests under `tests/Feature/Evaluation`, `tests/Feature`, and `tests/js` protect every boundary above.

---

### Task 1: Add the Activity-Evidence Database Relationship

**Files:**
- Create: `database/migrations/2026_07_25_000002_add_support_activity_entry_to_evidence_answers.php`
- Modify: `app/Models/EvidenceAnswer.php`
- Modify: `app/Models/SupportActivityEntry.php`
- Modify: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`
- Create: `tests/Feature/Evaluation/SupportActivityEvidenceMigrationTest.php`

**Interfaces:**
- Produces: `evidence_answers.support_activity_entry_id: ?int`.
- Produces: `EvidenceAnswer::supportActivityEntry(): BelongsTo`.
- Produces: `SupportActivityEntry::evidenceAnswers(): HasMany`.
- Cascade contract: deleting a support activity entry deletes its evidence rows.

- [ ] **Step 1: Write failing schema and migration-data tests**

Add a schema assertion:

```php
$this->assertTrue(Schema::hasColumn('evidence_answers', 'support_activity_entry_id'));
```

Add a migration behavior test that creates one activity-enabled criterion and
one disabled criterion, creates one support evidence row for each, invokes the
new migration's `down()` followed by `up()`, and asserts:

```php
$this->assertDatabaseMissing('evidence_answers', [
    'support_criteria_id' => $activityCriterion->id,
]);
$this->assertDatabaseHas('evidence_answers', [
    'support_criteria_id' => $staticCriterion->id,
    'link' => 'https://example.com/static',
]);
```

Add a cascade assertion by creating an activity entry and evidence row, then
deleting the activity entry:

```php
$entry->delete();
$this->assertDatabaseMissing('evidence_answers', [
    'support_activity_entry_id' => $entry->id,
]);
```

The production break caught is a missing foreign key, a broad data purge, or
the wrong delete action.

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEvidenceMigrationTest.php
```

Expected: FAIL because the column, migration, and model assignment do not yet
exist.

- [ ] **Step 3: Implement the migration and relationships**

Use a portable `whereExists` deletion before adding the foreign key:

```php
DB::table('evidence_answers')
    ->whereNotNull('support_criteria_id')
    ->whereExists(function ($query): void {
        $query->selectRaw('1')
            ->from('support_criterias')
            ->whereColumn('support_criterias.id', 'evidence_answers.support_criteria_id')
            ->where('support_criterias.allow_activity_entries', true);
    })
    ->delete();

Schema::table('evidence_answers', function (Blueprint $table): void {
    $table->foreignId('support_activity_entry_id')
        ->nullable()
        ->after('support_criteria_id')
        ->constrained('support_activity_entries')
        ->cascadeOnDelete();
});
```

The `down()` method must use
`$table->dropConstrainedForeignId('support_activity_entry_id')`.

Add `support_activity_entry_id` to `EvidenceAnswer::$fillable`, add its
`BelongsTo` relationship, and add this ordered relationship to
`SupportActivityEntry`:

```php
public function evidenceAnswers(): HasMany
{
    return $this->hasMany(EvidenceAnswer::class, 'support_activity_entry_id')
        ->orderBy('id');
}
```

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEvidenceMigrationTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit the schema unit**

```bash
git add database/migrations/2026_07_25_000002_add_support_activity_entry_to_evidence_answers.php app/Models/EvidenceAnswer.php app/Models/SupportActivityEntry.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEvidenceMigrationTest.php
git commit -m "feat: relate support evidence to activity entries"
```

---

### Task 2: Validate and Persist Nested Activity Evidence

**Files:**
- Modify: `app/Support/SupportScoreRules.php`
- Modify: `app/Services/SupportScoreService.php`
- Modify: `app/Services/SupportActivityEntryService.php`
- Modify: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Modify: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

**Interfaces:**
- Consumes: `activity_entries[*].evidence_links: array<int, string|null>`.
- Produces: normalized unique non-empty links on each activity item.
- Produces: `SupportActivityEntryService::persist()` replaces evidence for the exact persisted activity entry.
- Preserves: top-level `evidence_links` for non-activity criteria.

- [ ] **Step 1: Write failing persistence and required-evidence tests**

Add nested rules:

```php
'support_list.*.activity_entries.*.evidence_links' => ['nullable', 'array'],
'support_list.*.activity_entries.*.evidence_links.*' => ['nullable', 'url:http,https'],
```

Add service tests with two submitted activity entries:

```php
$this->persist([
    [
        'content' => '<p>โครงการหนึ่ง</p>',
        'evidence_links' => [' https://example.com/one ', 'https://example.com/one'],
    ],
    [
        'content' => '<p>โครงการสอง</p>',
        'evidence_links' => ['https://example.com/two'],
    ],
]);

$entries = SupportActivityEntry::with('evidenceAnswers')->orderBy('sequence')->get();
$this->assertSame(['https://example.com/one'], $entries[0]->evidenceAnswers->pluck('link')->all());
$this->assertSame(['https://example.com/two'], $entries[1]->evidenceAnswers->pluck('link')->all());
```

Add update and deletion assertions proving that replacing the first entry's
links does not alter the second entry's links, and deleting an activity removes
its links.

Add a required-evidence test with `allow_activity_entries = true` and
`require_evidence = true`:

```php
'activity_entries' => [
    ['content' => '<p>ไม่มีลิงก์</p>', 'evidence_links' => []],
    ['content' => '<p>มีลิงก์</p>', 'evidence_links' => ['https://example.com/proof']],
],
```

This payload must pass. A payload where every nested list is empty must fail
with `support_list.0.activity_entries`.

Add a regression test proving a disabled criterion still reads and replaces
top-level `evidence_links`.

The production breaks caught are cross-entry evidence replacement, requiring a
link on every entry, and accidentally removing the static-criterion path.

- [ ] **Step 2: Run focused tests and verify RED**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: FAIL because nested evidence is ignored and the required-evidence
check still reads only criterion-level links.

- [ ] **Step 3: Normalize nested evidence before validation**

In `SupportScoreService::persist()`, normalize both levels:

```php
$normalizeLinks = fn (array $links): array => collect($links)
    ->map(fn ($link) => trim((string) $link))
    ->filter()
    ->unique()
    ->values()
    ->all();

$item['evidence_links'] = $normalizeLinks($item['evidence_links'] ?? []);
$item['activity_entries'] = collect($item['activity_entries'] ?? [])
    ->map(function (array $entry) use ($normalizeLinks): array {
        $entry['evidence_links'] = $normalizeLinks($entry['evidence_links'] ?? []);
        return $entry;
    })
    ->values()
    ->all();
```

Add the nested validation rules shown in Step 1.

- [ ] **Step 4: Split required-evidence and criterion persistence by criterion type**

In `normalizeAndValidateItems()`, use the aggregate nested list for an
activity-enabled criterion:

```php
$hasEvidence = $criterion->allow_activity_entries
    ? collect($normalizedItems[$itemIndex]['activity_entries'] ?? [])
        ->contains(fn (array $entry): bool => ($entry['evidence_links'] ?? []) !== [])
    : $normalizedItems[$itemIndex]['evidence_links'] !== [];

if (! $hasEvidence) {
    throw ValidationException::withMessages([
        $criterion->allow_activity_entries
            ? "support_list.{$itemIndex}.activity_entries"
            : "support_list.{$itemIndex}.evidence_links" => [
                'กรุณาแนบหลักฐานสำหรับเกณฑ์นี้',
            ],
    ]);
}
```

Inside the transaction, delete and recreate criterion-level evidence only when
`! $criterion->allow_activity_entries`. Do not run a criterion-wide evidence
delete for activity-enabled criteria.

- [ ] **Step 5: Persist evidence beside each evaluatee activity entry**

After resolving `$entry` for both create and update paths in
`persistEvaluateeChanges()`, call one private method:

```php
private function replaceEvidence(
    SupportActivityEntry $entry,
    SupportCriteria $criterion,
    array $links
): void {
    $entry->evidenceAnswers()->delete();

    foreach ($links as $link) {
        $entry->evidenceAnswers()->create([
            'evaluation_list_id' => $criterion->evaluation_list_id,
            'support_criteria_id' => $criterion->id,
            'report_id' => $entry->report_id,
            'link' => $link,
        ]);
    }
}
```

Call it with `$entryData['evidence_links'] ?? []`. Existing entry IDs already
pass the report-and-criterion ownership check before this method runs.

For reviewer persistence, preserve submitted nested links using the same method
after validating entry IDs and indicator ownership. This retains the current
hidden-field evidence permission behavior without allowing links to move
between entries.

- [ ] **Step 6: Run focused tests and verify GREEN**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit the persistence unit**

```bash
git add app/Support/SupportScoreRules.php app/Services/SupportScoreService.php app/Services/SupportActivityEntryService.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: persist evidence per support activity"
```

---

### Task 3: Expose Nested Evidence in the Read Model and Export

**Files:**
- Modify: `app/Support/SupportCriteriaReadModel.php`
- Modify: `app/Exports/SingleReportExport.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`
- Modify: `tests/Feature/ReportsExportTest.php`

**Interfaces:**
- Produces: `activity_entries[*].evidence_links: array<int, string>`.
- Produces: criterion `evidence_links` only from rows whose `support_activity_entry_id` is null.
- Consumes in export: each activity entry's nested links.

- [ ] **Step 1: Write failing read-model and export tests**

Create two activity entries and one evidence row for each, then assert:

```php
$this->assertSame(
    ['https://example.com/first'],
    $item['activity_entries'][0]['evidence_links']
);
$this->assertSame(
    ['https://example.com/second'],
    $item['activity_entries'][1]['evidence_links']
);
$this->assertSame([], $item['evidence_links']);
```

Retain a separate assertion that a disabled criterion returns its null-entry
criterion evidence.

Change the export fixture to:

```php
'activity_entries' => [[
    'content' => '<p>โครงการเพิ่มเติม</p>',
    'evidence_links' => ['https://example.com/activity-evidence'],
]],
'evidence_links' => [],
```

Assert the evidence row occurs immediately after its owning activity row by
comparing their array indexes. The production break caught is flattening all
activity evidence back to the criterion.

- [ ] **Step 2: Run focused tests and verify RED**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ReportsExportTest.php
```

Expected: FAIL because activity entries do not expose evidence and export reads
only criterion links.

- [ ] **Step 3: Eager-load and map evidence by owner**

Load activity evidence with the entries:

```php
$activityEntries = SupportActivityEntry::query()
    ->with([
        'histories.modifierUser:id,prefix,name',
        'evidenceAnswers:id,evaluation_list_id,report_id,support_criteria_id,support_activity_entry_id,link',
    ])
```

Add this key to each mapped activity:

```php
'evidence_links' => $entry->evidenceAnswers
    ->pluck('link')
    ->filter()
    ->unique()
    ->values()
    ->all(),
```

Limit the criterion evidence query with
`->whereNull('support_activity_entry_id')`.

- [ ] **Step 4: Emit activity evidence directly after its activity**

Move the nested loop into the activity loop:

```php
foreach ($supportItem['activity_entries'] ?? [] as $activityEntry) {
    $data[] = [
        '  กิจกรรม/โครงการเพิ่มเติม',
        SafeHtml::plainText($activityEntry['content'] ?? ''),
    ];

    foreach ($activityEntry['evidence_links'] ?? [] as $evidenceLink) {
        $data[] = ['    หลักฐาน', $evidenceLink];
    }
}

foreach ($supportItem['evidence_links'] ?? [] as $evidenceLink) {
    $data[] = ['  หลักฐาน', $evidenceLink];
}
```

The final loop intentionally preserves disabled-criterion exports.

- [ ] **Step 5: Run focused tests and verify GREEN**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ReportsExportTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit the read-model unit**

```bash
git add app/Support/SupportCriteriaReadModel.php app/Exports/SingleReportExport.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ReportsExportTest.php
git commit -m "feat: expose activity evidence in reports"
```

---

### Task 4: Render Evidence at the End of Every Activity Card

**Files:**
- Modify: `resources/views/components/support-activity-entry-editor.blade.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: each activity entry's `evidence_links`.
- Produces editable names:
  `support_list[<criterion>][activity_entries][<entry>][evidence_links][]`.
- Produces selectors scoped to an activity:
  `data-support-evidence-section`, `data-support-evidence-container`,
  `data-add-support-evidence`, `data-support-evidence-input`.

- [ ] **Step 1: Write failing Blade behavior tests**

Set `supportActivityViewItem()['activity_entries'][0]['evidence_links']` to
`['https://example.com/activity-proof']`. Assert editable output contains:

```php
->toContain('support_list[7][activity_entries][0][evidence_links][]')
->toContain('href="https://example.com/activity-proof"')
->not->toContain('name="support_list[7][evidence_links][]"')
```

Assert the evidence section appears inside
`data-support-activity-entry` after the activity content. Add a second test
using `supportViewItem()` to prove a disabled criterion still contains
`support_list[7][evidence_links][]`.

Render a read-only activity item and assert safe external-link attributes and
no editable input. The production break caught is returning to one
criterion-level field or omitting nested links from grouped cards.

- [ ] **Step 2: Run the view test and verify RED**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: FAIL because evidence controls are still criterion-level.

- [ ] **Step 3: Add a complete evidence footer to the shared activity editor**

Extend the component props with `evidenceEditable` and calculate:

```blade
@php
    $canEditEvidence = $canEditActivities
        && $evidenceEditable
        && $activityEntryRole === 'evaluatee';
    $evidenceLinks = array_values(array_filter($entry['evidence_links'] ?? []));
@endphp
```

At the end of `support-activity-entry-editor.blade.php`, render:

```blade
<section class="mt-4 border-t border-slate-200 pt-4" data-support-evidence-section>
    <div class="flex items-center justify-between gap-3">
        <h6 class="text-sm font-semibold text-slate-700">หลักฐาน</h6>
        @if ($canEditEvidence)
            <button type="button" data-add-support-evidence
                class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900">
                + เพิ่มลิงก์หลักฐาน
            </button>
        @endif
    </div>
    <div class="mt-2 space-y-2" data-support-evidence-container>
        @if ($canEditEvidence)
            @foreach ($evidenceLinks ?: [''] as $link)
                <div class="support-evidence-row flex items-center gap-2">
                    <input type="url" data-support-evidence-input
                        name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][evidence_links][]"
                        value="{{ $link }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5"
                        placeholder="https://example.com/evidence">
                    <button type="button" data-remove-support-evidence
                        class="rounded-lg bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700">
                        ลบ
                    </button>
                </div>
            @endforeach
        @else
            @forelse ($evidenceLinks as $link)
                <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                    class="block break-all text-sm font-semibold text-blue-700 underline">
                    {{ $link }}
                </a>
                @if ($canEditActivities)
                    <input type="hidden" data-support-evidence-input
                        name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][evidence_links][]"
                        value="{{ $link }}">
                @endif
            @empty
                <p class="text-sm text-slate-400">ไม่มีหลักฐาน</p>
            @endforelse
        @endif
    </div>
</section>
```

- [ ] **Step 4: Apply the same footer to the ungrouped inline activity card**

The ungrouped branch currently duplicates activity markup in
`support-criteria-table.blade.php`. Replace that duplicate with
`<x-support-activity-entry-editor>` so grouped and ungrouped entries share the
same evidence footer and permission rules.

Insert `@if (empty($item['allow_activity_entries']))` immediately before the
criterion-level `<div class="mt-4" data-support-evidence-section>` and insert
the matching `@endif` immediately after that section's closing `</div>`, before
the modification-reason label.

Pass `:evidence-editable="$evidenceEditable"` to every activity editor.

- [ ] **Step 5: Run the view test and verify GREEN**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit the rendered UI unit**

```bash
git add resources/views/components/support-activity-entry-editor.blade.php resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: render evidence on support activities"
```

---

### Task 5: Create, Reindex, Validate, and Restore Nested Evidence in JavaScript

**Files:**
- Modify: `resources/js/support-activity-entries.js`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `tests/js/support-activity-entries.test.mjs`

**Interfaces:**
- Produces: `activityEvidenceFieldName(criterionId, entryIndex)`.
- Consumes: evidence containers found inside the closest
  `[data-support-activity-entry]`.
- Preserves: criterion-level controls for disabled criteria.

- [ ] **Step 1: Write failing pure-function tests**

Add:

```js
assert.equal(
    activityEvidenceFieldName(7, 2),
    'support_list[7][activity_entries][2][evidence_links][]',
);
```

Add an assertion that `activityEntryFieldName(7, 2, 'content')` remains
unchanged. The production break caught is a nested evidence input retaining a
stale entry index after another activity is removed.

- [ ] **Step 2: Run the JavaScript test and verify RED**

Run:

```bash
node --test tests/js/support-activity-entries.test.mjs
```

Expected: FAIL because `activityEvidenceFieldName` is not exported.

- [ ] **Step 3: Add the field-name helper**

Implement and export:

```js
export function activityEvidenceFieldName(criterionId, entryIndex) {
    return `support_list[${criterionId}][activity_entries][${entryIndex}][evidence_links][]`;
}
```

Expose it on `window.SupportActivityEntries`.

- [ ] **Step 4: Make row creation and reindexing activity-aware**

Change `createEvidenceRow` to accept `(criterionId, entryIndex)` and use
`activityEvidenceFieldName` when it is called inside an activity card. Keep a
criterion-level mode for disabled criteria.

The activity row template created by `createActivityEntryRow()` must append an
evidence footer with one empty evidence row. In `reindexActivityEntries(item)`,
after updating content, ID, indicator, and reason names, run:

```js
entry.querySelectorAll('[data-support-evidence-input]').forEach((input) => {
    input.name = activityTools().activityEvidenceFieldName(
        item.dataset.supportId,
        entryIndex,
    );
});
```

The add-evidence click handler must locate
`addButton.closest('[data-support-evidence-section]')` and append to that
section's container rather than looking up one criterion-wide container ID.

- [ ] **Step 5: Scope validation, snapshots, and live summaries correctly**

For required evidence, collect all evidence inputs below the criterion; this
preserves the aggregate rule. When selecting the first invalid control, choose
the first activity's add button or URL input.

Change activity snapshots to include each row's evidence values:

```js
{
    id,
    indicatorItemId,
    content,
    reason,
    evidenceLinks: Array.from(
        entry.querySelectorAll('[data-support-evidence-input]'),
    ).map((input) => input.value),
}
```

During restore, rebuild evidence rows inside the corresponding activity card.
When refreshing desktop/mobile summary evidence, flatten nested activity links
only for the criterion summary display; do not alter their form ownership.

- [ ] **Step 6: Run JavaScript tests and production build**

Run:

```bash
npm run test:js
npm run build
```

Expected: all JavaScript tests PASS and Vite exits 0.

- [ ] **Step 7: Commit the interaction unit**

```bash
git add resources/js/support-activity-entries.js resources/views/components/support-criteria-table-script.blade.php tests/js/support-activity-entries.test.mjs
git commit -m "feat: manage nested support evidence controls"
```

---

### Task 6: Verify End-to-End Support Evaluation Behavior

**Files:**
- Modify only if a regression is found: the smallest file from Tasks 1–5 that owns the failing behavior.

**Interfaces:**
- Verifies all interfaces produced by Tasks 1–5 together.

- [ ] **Step 1: Run the focused PHP suite**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEvidenceMigrationTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/ReportsExportTest.php
```

Expected: PASS with no warnings or errors.

- [ ] **Step 2: Run the broader support flow suite**

Run:

```bash
php artisan test tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS. Update old payload fixtures so activity-enabled criteria put
links under `activity_entries[*].evidence_links`; keep top-level links only for
disabled criteria.

- [ ] **Step 3: Run all JavaScript tests and the build**

Run:

```bash
npm run test:js
npm run build
```

Expected: PASS and Vite exits 0.

- [ ] **Step 4: Run formatting checks on changed production files**

Run:

```bash
vendor/bin/pint --test app/Models/EvidenceAnswer.php app/Models/SupportActivityEntry.php app/Support/SupportScoreRules.php app/Support/SupportCriteriaReadModel.php app/Services/SupportScoreService.php app/Services/SupportActivityEntryService.php app/Exports/SingleReportExport.php database/migrations/2026_07_25_000002_add_support_activity_entry_to_evidence_answers.php
npx prettier --check resources/js/support-activity-entries.js resources/views/components/support-activity-entry-editor.blade.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php
```

Expected: both commands exit 0.

- [ ] **Step 5: Inspect the final diff and migration scope**

Run:

```bash
git diff --check
git status --short
git diff --stat
```

Confirm that unrelated pre-existing worktree changes were not staged or
modified by this implementation. Confirm the migration deletion predicate
contains both `support_criteria_id IS NOT NULL` and
`allow_activity_entries = true`.

- [ ] **Step 6: Commit any regression-fixture adjustments**

If Step 2 required only fixture changes, commit exactly those test files:

```bash
git add tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
git commit -m "test: update support activity evidence flows"
```

If no files changed, skip this commit.
