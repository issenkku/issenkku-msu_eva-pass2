# Support Indicator Grouped Projects Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let Admin define structured support indicator items and let evaluatees add any number of projects under exactly one indicator item.

**Architecture:** Normalize grouped indicator definitions into `support_indicator_items` and attach each grouped `support_activity_entries` row through a nullable foreign key. Keep legacy criteria on the existing `indicator` Rich Text path, expose both modes through one read model, and render grouped projects in the existing shared support Modal without changing score, evidence, comment, or report-flow services.

**Tech Stack:** Laravel 11, Eloquent, MySQL/SQLite migrations, Blade, Summernote, vanilla JavaScript, Pest, Node test runner, Vite

## Global Constraints

- Work directly on `feat/support`; do not create a worktree.
- One project belongs to exactly one indicator item; one indicator item may own unlimited projects.
- Empty indicator groups are valid and must not block draft saving or submission.
- Grouped criteria use `support_indicator_items`; legacy criteria continue using `support_criterias.indicator`.
- Scores, weights, evidence, comments, reviewer reasons/history, statuses, draft saving, and submission flow remain unchanged.
- Reviewers may edit existing project content with a reason but may not add, delete, move, or reorder projects.
- Preserve unrelated working-tree changes, especially the existing import-only diff in `app/Http/Controllers/ReportStructureController.php`, and stage feature hunks deliberately.

---

### Task 1: Add the grouped-indicator schema and model relationships

**Files:**
- Create: `database/migrations/2026_07_21_000002_create_support_indicator_items.php`
- Create: `app/Models/SupportIndicatorItem.php`
- Modify: `app/Models/SupportCriteria.php`
- Modify: `app/Models/SupportActivityEntry.php`
- Test: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`

**Interfaces:**
- Produces: `SupportIndicatorItem`, `SupportCriteria::indicatorItems()`, `SupportActivityEntry::indicatorItem()`, `support_criterias.group_activity_entries_by_indicator`, and `support_activity_entries.support_indicator_item_id`.
- Consumers: Tasks 2-6 use these exact model and column names.

- [ ] **Step 1: Write failing schema and relationship tests**

Add imports and this test to `SupportEvaluationSchemaTest.php`:

```php
use App\Models\SupportIndicatorItem;

public function test_support_indicator_items_schema_and_relations_exist(): void
{
    $this->assertTrue(Schema::hasColumn(
        'support_criterias',
        'group_activity_entries_by_indicator'
    ));
    $this->assertTrue(Schema::hasTable('support_indicator_items'));
    $this->assertTrue(Schema::hasColumns('support_indicator_items', [
        'id',
        'support_criteria_id',
        'sequence',
        'code',
        'description',
    ]));
    $this->assertTrue(Schema::hasColumn(
        'support_activity_entries',
        'support_indicator_item_id'
    ));

    $indicatorColumn = collect(Schema::getColumns('support_criterias'))
        ->firstWhere('name', 'indicator');

    $this->assertTrue((bool) $indicatorColumn['nullable']);
    $this->assertInstanceOf(
        SupportIndicatorItem::class,
        (new SupportCriteria)->indicatorItems()->getModel()
    );
    $this->assertInstanceOf(
        SupportIndicatorItem::class,
        (new SupportActivityEntry)->indicatorItem()->getModel()
    );
}
```

- [ ] **Step 2: Run the schema test and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportEvaluationSchemaTest.php --filter="support indicator items"
```

Expected: FAIL because the table, columns, model, and relationships do not exist.

- [ ] **Step 3: Implement the migration and models**

Create an idempotent migration. Use explicit short foreign-key names so MySQL's 64-character identifier limit is never exceeded:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indicator = collect(Schema::getColumns('support_criterias'))
            ->firstWhere('name', 'indicator');

        if ($indicator && ! $indicator['nullable']) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->text('indicator')->nullable()->change();
            });
        }

        if (! Schema::hasColumn('support_criterias', 'group_activity_entries_by_indicator')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->boolean('group_activity_entries_by_indicator')
                    ->default(false)
                    ->after('allow_activity_entries');
            });
        }

        if (! Schema::hasTable('support_indicator_items')) {
            Schema::create('support_indicator_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('support_criteria_id');
                $table->unsignedInteger('sequence');
                $table->string('code', 50);
                $table->text('description');
                $table->timestamps();
                $table->foreign('support_criteria_id', 'support_indicator_criterion_fk')
                    ->references('id')->on('support_criterias')->cascadeOnDelete();
                $table->unique(
                    ['support_criteria_id', 'code'],
                    'support_indicator_criterion_code_unique'
                );
                $table->index(
                    ['support_criteria_id', 'sequence'],
                    'support_indicator_criterion_sequence_index'
                );
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'support_indicator_item_id')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->unsignedBigInteger('support_indicator_item_id')
                    ->nullable()
                    ->after('support_criteria_id');
                $table->foreign('support_indicator_item_id', 'support_activity_entry_indicator_fk')
                    ->references('id')->on('support_indicator_items')->restrictOnDelete();
            });
        }

        $this->ensureForeignKeys();
    }

    public function down(): void
    {
        if (Schema::hasColumn('support_activity_entries', 'support_indicator_item_id')) {
            $hasIndicatorForeignKey = $this->hasForeignKeyForColumn(
                Schema::getForeignKeys('support_activity_entries'),
                'support_indicator_item_id'
            );
            Schema::table('support_activity_entries', function (Blueprint $table) use ($hasIndicatorForeignKey): void {
                if ($hasIndicatorForeignKey) {
                    $table->dropForeign('support_activity_entry_indicator_fk');
                }
                $table->dropColumn('support_indicator_item_id');
            });
        }

        if (Schema::hasColumn('support_criterias', 'group_activity_entries_by_indicator')) {
            DB::table('support_criterias')
                ->whereNull('indicator')
                ->orderBy('id')
                ->eachById(function ($criteria): void {
                    $indicator = DB::table('support_indicator_items')
                        ->where('support_criteria_id', $criteria->id)
                        ->orderBy('sequence')
                        ->get()
                        ->map(fn ($item) => '<div><strong>'.e($item->code).'</strong> '.$item->description.'</div>')
                        ->implode('');

                    DB::table('support_criterias')
                        ->where('id', $criteria->id)
                        ->update(['indicator' => $indicator !== '' ? $indicator : $criteria->activity_name]);
                });

            Schema::dropIfExists('support_indicator_items');

            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->dropColumn('group_activity_entries_by_indicator');
                $table->text('indicator')->nullable(false)->change();
            });
        } else {
            Schema::dropIfExists('support_indicator_items');
        }
    }

    private function ensureForeignKeys(): void
    {
        $itemForeignKeys = Schema::getForeignKeys('support_indicator_items');
        if (! $this->hasForeignKeyForColumn($itemForeignKeys, 'support_criteria_id')) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->foreign('support_criteria_id', 'support_indicator_criterion_fk')
                    ->references('id')->on('support_criterias')->cascadeOnDelete();
            });
        }

        $entryForeignKeys = Schema::getForeignKeys('support_activity_entries');
        if (! $this->hasForeignKeyForColumn($entryForeignKeys, 'support_indicator_item_id')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->foreign('support_indicator_item_id', 'support_activity_entry_indicator_fk')
                    ->references('id')->on('support_indicator_items')->restrictOnDelete();
            });
        }
    }

    /** @param array<int, array{columns: array<int, string>}> $foreignKeys */
    private function hasForeignKeyForColumn(array $foreignKeys, string $column): bool
    {
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
```

Create `SupportIndicatorItem.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportIndicatorItem extends Model
{
    protected $fillable = [
        'support_criteria_id',
        'sequence',
        'code',
        'description',
    ];

    public function supportCriteria(): BelongsTo
    {
        return $this->belongsTo(SupportCriteria::class);
    }

    public function activityEntries(): HasMany
    {
        return $this->hasMany(SupportActivityEntry::class);
    }
}
```

Add this exact fillable entry, cast entry, and relationship to `SupportCriteria`:

```php
'group_activity_entries_by_indicator',

'group_activity_entries_by_indicator' => 'boolean',

public function indicatorItems(): HasMany
{
    return $this->hasMany(SupportIndicatorItem::class)->orderBy('sequence');
}
```

Add this exact fillable entry and relationship to `SupportActivityEntry`:

```php
'support_indicator_item_id',

public function indicatorItem(): BelongsTo
{
    return $this->belongsTo(SupportIndicatorItem::class, 'support_indicator_item_id');
}
```

- [ ] **Step 4: Run schema tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
```

Expected: all schema, cascade, and relationship tests pass.

- [ ] **Step 5: Commit the schema slice**

```powershell
git add -- database/migrations/2026_07_21_000002_create_support_indicator_items.php app/Models/SupportIndicatorItem.php app/Models/SupportCriteria.php app/Models/SupportActivityEntry.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
git diff --cached --check
git commit -m "feat: add structured support indicator schema"
```

---

### Task 2: Persist and protect Admin indicator items

**Files:**
- Create: `app/Services/SupportIndicatorItemService.php`
- Modify: `app/Http/Controllers/ReportStructureController.php:160-190,299-310,363-371,530-540,621-629,955-985`
- Modify: `app/Models/SupportCriteria.php`
- Test: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Consumes: `SupportCriteria::indicatorItems()` and `SupportIndicatorItem::activityEntries()` from Task 1.
- Produces: nested Admin payload `support_criterias.*.indicator_items`, conditional `indicator`, show-response fields, and `SupportIndicatorItemService::sync(SupportCriteria $criterion, bool $grouped, array $items, bool $wasGrouped): void`.

- [ ] **Step 1: Write failing Admin create/show/update validation tests**

Add tests that create a grouped criterion and verify its normalized response:

```php
public function test_admin_can_create_and_show_grouped_support_indicator_items(): void
{
    $response = $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => '<p>งานวิจัย</p>',
        'indicator' => null,
        'target_value' => 100,
        'weight' => 100,
        'allow_activity_entries' => true,
        'group_activity_entries_by_indicator' => true,
        'indicator_items' => [
            ['sequence' => 1, 'code' => '2.1', 'description' => '<p>ดำเนินการวิจัย</p>'],
            ['sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่งานวิจัย</p>'],
        ],
    ]]))->assertCreated();

    $criterion = SupportCriteria::with('indicatorItems')->firstOrFail();
    $this->assertNull($criterion->indicator);
    $this->assertTrue($criterion->group_activity_entries_by_indicator);
    $this->assertSame(['2.1', '2.2'], $criterion->indicatorItems->pluck('code')->all());

    $this->getJson(route('report-structure.show', $response->json('data.id')))
        ->assertOk()
        ->assertJsonPath(
            'data.categories.0.evaluation_lists.0.support_criterias.0.indicator_items.1.code',
            '2.2'
        );
}
```

Add this validation test:

```php
public function test_grouped_support_indicator_configuration_is_validated(): void
{
    $base = [
        'sequence' => 1,
        'activity_name' => '<p>งานวิจัย</p>',
        'indicator' => null,
        'target_value' => 100,
        'weight' => 100,
        'allow_activity_entries' => true,
        'group_activity_entries_by_indicator' => true,
        'indicator_items' => [[
            'sequence' => 1,
            'code' => '2.1',
            'description' => '<p>ดำเนินการวิจัย</p>',
        ]],
    ];

    $cases = [
        'missing items' => [
            fn (array $criterion) => array_replace($criterion, ['indicator_items' => []]),
            'categories.0.evaluation_lists.0.support_criterias.0.indicator_items',
        ],
        'duplicate codes' => [
            fn (array $criterion) => array_replace($criterion, ['indicator_items' => [
                ['sequence' => 1, 'code' => '2.1', 'description' => '<p>หนึ่ง</p>'],
                ['sequence' => 2, 'code' => '2.1', 'description' => '<p>สอง</p>'],
            ]]),
            'categories.0.evaluation_lists.0.support_criterias.0.indicator_items.1.code',
        ],
        'activities disabled' => [
            fn (array $criterion) => array_replace($criterion, ['allow_activity_entries' => false]),
            'categories.0.evaluation_lists.0.support_criterias.0.group_activity_entries_by_indicator',
        ],
        'legacy indicator missing' => [
            fn (array $criterion) => array_replace($criterion, [
                'group_activity_entries_by_indicator' => false,
                'indicator_items' => [],
            ]),
            'categories.0.evaluation_lists.0.support_criterias.0.indicator',
        ],
    ];

    foreach ($cases as [$mutate, $errorKey]) {
        $this->postJson(
            route('report-structure.store'),
            $this->payload([$mutate($base)])
        )->assertUnprocessable()->assertJsonValidationErrors($errorKey);
    }
}
```

- [ ] **Step 2: Run Admin tests and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="grouped support indicator|grouped criterion"
```

Expected: FAIL because grouped fields are ignored and nested items are not persisted.

- [ ] **Step 3: Implement the synchronization service**

Create `SupportIndicatorItemService.php`:

```php
<?php

namespace App\Services;

use App\Models\SupportCriteria;
use Illuminate\Validation\ValidationException;

class SupportIndicatorItemService
{
    /** @param array<int, array<string, mixed>> $items */
    public function sync(
        SupportCriteria $criterion,
        bool $grouped,
        array $items,
        bool $wasGrouped
    ): void
    {
        $existing = $criterion->indicatorItems()->withCount('activityEntries')->get()->keyBy('id');

        if (! $grouped) {
            if ($wasGrouped
                && $existing->contains(fn ($item) => $item->activity_entries_count > 0)) {
                throw ValidationException::withMessages([
                    'support_criterias' => ['ไม่สามารถปิดการแบ่งตามตัวชี้วัดย่อยได้ เนื่องจากมีโครงการอ้างอิงอยู่'],
                ]);
            }

            return;
        }

        $keptIds = [];
        foreach (array_values($items) as $index => $data) {
            $id = isset($data['support_indicator_item_id'])
                ? (int) $data['support_indicator_item_id']
                : null;
            $attributes = [
                'sequence' => $index + 1,
                'code' => trim((string) $data['code']),
                'description' => (string) $data['description'],
            ];

            if ($id) {
                $item = $existing->get($id);
                if (! $item) {
                    throw ValidationException::withMessages([
                        'support_criterias' => ['ไม่พบตัวชี้วัดย่อยในเกณฑ์นี้'],
                    ]);
                }
                $item->update($attributes);
            } else {
                $item = $criterion->indicatorItems()->create($attributes);
            }
            $keptIds[] = $item->id;
        }

        $removed = $existing->reject(fn ($item) => in_array($item->id, $keptIds, true));
        if ($removed->contains(fn ($item) => $item->activity_entries_count > 0)) {
            throw ValidationException::withMessages([
                'support_criterias' => ['ไม่สามารถลบตัวชี้วัดย่อยที่มีโครงการอ้างอิงอยู่'],
            ]);
        }
        $removed->each->delete();
    }
}
```

- [ ] **Step 4: Integrate conditional validation, create/update, and show mapping**

Add nested rules to both controller validation arrays:

```php
'categories.*.evaluation_lists.*.support_criterias.*.indicator' => ['nullable', 'string', new HasRichText],
'categories.*.evaluation_lists.*.support_criterias.*.group_activity_entries_by_indicator' => ['sometimes', 'boolean'],
'categories.*.evaluation_lists.*.support_criterias.*.indicator_items' => ['sometimes', 'array'],
'categories.*.evaluation_lists.*.support_criterias.*.indicator_items.*.support_indicator_item_id' => ['sometimes', 'nullable', 'integer', 'exists:support_indicator_items,id'],
'categories.*.evaluation_lists.*.support_criterias.*.indicator_items.*.sequence' => ['required', 'integer', 'min:1'],
'categories.*.evaluation_lists.*.support_criterias.*.indicator_items.*.code' => ['required', 'string', 'max:50'],
'categories.*.evaluation_lists.*.support_criterias.*.indicator_items.*.description' => ['required', 'string', new HasRichText],
```

After `$request->validate(...)`, call a private controller method that loops every support criterion and builds nested errors with these exact conditions:

```php
$grouped && ! $allowActivityEntries
$grouped && count($indicatorItems) === 0
$grouped && count($codes) !== count(array_unique($codes))
! $grouped && SafeHtml::plainText((string) ($supportData['indicator'] ?? '')) === ''
```

Import `App\Support\SafeHtml`. Store duplicate-code errors under the second duplicated item's `.code` key. Throw `ValidationException::withMessages($errors)` once after walking all categories.

For create/update attributes, capture the persisted mode before updating and then build the new values:

```php
$wasGrouped = (bool) $supportCriteria->group_activity_entries_by_indicator;
$grouped = (bool) ($supportData['group_activity_entries_by_indicator'] ?? false);
$attributes = [
    'sequence' => $supportData['sequence'],
    'activity_name' => $supportData['activity_name'],
    'indicator' => $grouped ? null : $supportData['indicator'],
    'target_value' => $supportData['target_value'],
    'weight' => $supportData['weight'],
    'require_evidence' => (bool) ($supportData['require_evidence'] ?? false),
    'allow_activity_entries' => (bool) ($supportData['allow_activity_entries'] ?? false),
    'group_activity_entries_by_indicator' => $grouped,
];
```

Call the service inside the existing transaction after creating/updating each criterion:

```php
$indicatorItemService->sync(
    $supportCriteria,
    $grouped,
    $supportData['indicator_items'] ?? [],
    $wasGrouped
);
```

For creation, set `$wasGrouped = false` before creating the criterion. For update, read it from the loaded model before calling `update($attributes)`. This ensures disabling grouped mode cannot lose the previous-state signal.

Eager-load `categories.evaluationLists.supportCriterias.indicatorItems` and map:

```php
'group_activity_entries_by_indicator' => (bool) $supportCriteria->group_activity_entries_by_indicator,
'indicator_items' => $supportCriteria->indicatorItems->map(fn ($item) => [
    'support_indicator_item_id' => $item->id,
    'sequence' => $item->sequence,
    'code' => $item->code,
    'description' => $item->description,
])->values()->all(),
```

- [ ] **Step 5: Run Admin tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: all legacy and grouped template tests pass.

- [ ] **Step 6: Commit the Admin persistence slice**

Stage the service and tests normally. Use `git add -p app/Http/Controllers/ReportStructureController.php` and exclude the unrelated import reorder hunk.

```powershell
git add -- app/Services/SupportIndicatorItemService.php app/Models/SupportCriteria.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git add -p app/Http/Controllers/ReportStructureController.php
git diff --cached --check
git commit -m "feat: persist grouped support indicators"
```

---

### Task 3: Add Admin grouped-indicator controls

**Files:**
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php`
- Test: `tests/Feature/SupportCriteriaTemplateViewTest.php`
- Test: `tests/Feature/SupportCriteriaRichTextEditorTest.php`

**Interfaces:**
- Consumes: Admin payload fields from Task 2.
- Produces: `.support_group_by_indicator`, `.support_indicator_items`, `.support_indicator_item_block`, `.support_indicator_item_id`, `.support_indicator_code`, and `.support_indicator_description` DOM contracts.

- [ ] **Step 1: Write failing Admin Blade contract tests**

Extend the view tests with:

```php
expect($html)
    ->toContain('support_group_by_indicator')
    ->toContain('แยกโครงการตามตัวชี้วัดย่อย')
    ->toContain('support_indicator_items')
    ->toContain('support_indicator_item_block')
    ->toContain('support_indicator_code')
    ->toContain('support_indicator_description')
    ->toContain('เพิ่มตัวชี้วัดย่อย');
```

Assert create/edit scripts collect `group_activity_entries_by_indicator`, `indicator_items`, and `support_indicator_item_id`, and that Rich Text lifecycle hooks include `.support_indicator_description`.

- [ ] **Step 2: Run view tests and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php
```

Expected: FAIL because grouped controls and script contracts are missing.

- [ ] **Step 3: Add the Admin markup**

Wrap the legacy indicator label with `data-support-legacy-indicator`. Add the grouping checkbox after `support_allow_activity_entries`, then add a hidden grouped section:

```blade
<label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
    <input type="checkbox"
        class="support_group_by_indicator h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
    <span>แยกโครงการตามตัวชี้วัดย่อย</span>
</label>

<section class="support_indicator_items mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <h6 class="font-semibold text-amber-950">ตัวชี้วัดย่อย</h6>
        <button type="button" class="add_support_indicator_item_btn rounded-lg bg-white px-3 py-2 text-sm font-semibold text-amber-900">
            + เพิ่มตัวชี้วัดย่อย
        </button>
    </div>
    <div class="support_indicator_item_list space-y-3">
        <article class="support_indicator_item_block rounded-lg border border-amber-200 bg-white p-3" draggable="true">
            <input type="hidden" class="support_indicator_item_id">
            <input type="hidden" class="support_indicator_sequence" value="1">
            <label class="block text-sm font-medium text-gray-700">
                รหัสข้อ
                <input type="text" maxlength="50" class="support_indicator_code mt-2 block w-full rounded-lg border border-gray-300 p-2.5" placeholder="2.1">
            </label>
            <label class="mt-3 block text-sm font-medium text-gray-700">
                รายละเอียดตัวชี้วัด
                <textarea rows="5" class="support_indicator_description richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"></textarea>
            </label>
            <button type="button" class="delete_support_indicator_item_btn mt-3 text-sm font-semibold text-red-600">ลบข้อย่อย</button>
        </article>
    </div>
</section>
```

- [ ] **Step 4: Add create/edit behavior and payload collection**

Implement shared behavior in both create and edit scripts:

```javascript
function toggleSupportIndicatorMode(block) {
    const allow = block.querySelector('.support_allow_activity_entries');
    const grouped = block.querySelector('.support_group_by_indicator');
    if (!allow.checked) grouped.checked = false;
    grouped.disabled = !allow.checked;
    block.querySelector('[data-support-legacy-indicator]')
        .classList.toggle('hidden', grouped.checked);
    block.querySelector('.support_indicator_items')
        .classList.toggle('hidden', !grouped.checked);
}

function collectSupportIndicatorItems(block) {
    return Array.from(block.querySelectorAll('.support_indicator_item_block'))
        .map((item, index) => ({
            ...(item.querySelector('.support_indicator_item_id').value
                ? { support_indicator_item_id: Number(item.querySelector('.support_indicator_item_id').value) }
                : {}),
            sequence: index + 1,
            code: item.querySelector('.support_indicator_code').value.trim(),
            description: getRichTextValue(item.querySelector('.support_indicator_description')),
        }));
}
```

When collecting a support criterion:

```javascript
const grouped = supportBlock.querySelector('.support_group_by_indicator')?.checked || false;
const indicatorItems = grouped ? collectSupportIndicatorItems(supportBlock) : [];
const codes = indicatorItems.map((item) => item.code);
if (grouped && (indicatorItems.length === 0
    || indicatorItems.some((item) => !item.code || !hasVisibleRichText(item.description))
    || new Set(codes).size !== codes.length)) {
    throw new Error(`กรุณากรอกตัวชี้วัดย่อยของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ให้ครบและไม่ใช้รหัสซ้ำ`);
}

supportPayload.indicator = grouped ? null : indicator;
supportPayload.group_activity_entries_by_indicator = grouped;
supportPayload.indicator_items = indicatorItems;
```

Populate IDs, code, description, grouping checkbox, and call `toggleSupportIndicatorMode(block)`. Add/delete handlers must clone/reset Summernote safely, reindex `.support_indicator_sequence`, require at least one row while grouping is active, and call `markDirty()`.

- [ ] **Step 5: Run Admin view tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php
```

Expected: all Admin markup, collection, population, and Summernote contract tests pass.

- [ ] **Step 6: Commit the Admin UI slice**

```powershell
git add -- resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php
git diff --cached --check
git commit -m "feat: configure grouped support indicators"
```

---

### Task 4: Validate and persist each project's indicator assignment

**Files:**
- Modify: `app/Support/SupportScoreRules.php`
- Modify: `app/Services/SupportScoreService.php`
- Modify: `app/Services/SupportActivityEntryService.php`
- Modify: `app/Models/SupportActivityEntry.php`
- Test: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`

**Interfaces:**
- Consumes: `support_indicator_item_id` from Task 1 and grouped criterion flags/items from Task 2.
- Produces: evaluatee assignment validation and reviewer immutability for `activity_entries.*.support_indicator_item_id`.

- [ ] **Step 1: Write failing service tests**

Add the create-many test below:

```php
public function test_evaluatee_can_create_many_projects_under_one_indicator_and_another_group(): void
{
    $this->criterion->update([
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
    ]);
    $first = $this->criterion->indicatorItems()->create([
        'sequence' => 1, 'code' => '2.1', 'description' => '<p>วิจัย</p>',
    ]);
    $second = $this->criterion->indicatorItems()->create([
        'sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่</p>',
    ]);

    $this->persist([
        ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ A</p>'],
        ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ B</p>'],
        ['support_indicator_item_id' => $second->id, 'content' => '<p>โครงการ C</p>'],
    ]);

    $this->assertSame(
        [$first->id, $first->id, $second->id],
        SupportActivityEntry::query()->orderBy('sequence')
            ->pluck('support_indicator_item_id')->all()
    );
}
```

Add these exact validation tests after the create-many test:

```php
public function test_grouped_project_requires_an_indicator_from_the_same_criterion(): void
{
    $this->criterion->update([
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
    ]);
    $otherCriterion = SupportCriteria::create([
        'evaluation_list_id' => $this->criterion->evaluation_list_id,
        'sequence' => 2,
        'activity_name' => 'เกณฑ์อื่น',
        'indicator' => null,
        'target_value' => 100,
        'weight' => 20,
        'allow_activity_entries' => true,
        'group_activity_entries_by_indicator' => true,
    ]);
    $foreignItem = $otherCriterion->indicatorItems()->create([
        'sequence' => 1,
        'code' => '9.1',
        'description' => '<p>ข้ออื่น</p>',
    ]);

    foreach ([null, $foreignItem->id] as $indicatorItemId) {
        try {
            $this->persist([[
                'support_indicator_item_id' => $indicatorItemId,
                'content' => '<p>โครงการ</p>',
            ]]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'support_list.0.activity_entries.0.support_indicator_item_id',
                $exception->errors()
            );
        }
    }
}

public function test_existing_project_cannot_move_to_another_indicator_item(): void
{
    $this->criterion->update([
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
    ]);
    $first = $this->criterion->indicatorItems()->create([
        'sequence' => 1, 'code' => '2.1', 'description' => '<p>หนึ่ง</p>',
    ]);
    $second = $this->criterion->indicatorItems()->create([
        'sequence' => 2, 'code' => '2.2', 'description' => '<p>สอง</p>',
    ]);
    $entry = SupportActivityEntry::create([
        'report_id' => $this->report->id,
        'support_criteria_id' => $this->criterion->id,
        'support_indicator_item_id' => $first->id,
        'sequence' => 1,
        'content' => '<p>โครงการเดิม</p>',
    ]);

    foreach (['persist', 'persistAsReviewer'] as $method) {
        try {
            $this->{$method}([[
                'id' => $entry->id,
                'support_indicator_item_id' => $second->id,
                'content' => '<p>โครงการเดิม</p>',
            ]]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'support_list.0.activity_entries.0.support_indicator_item_id',
                $exception->errors()
            );
        }
    }
}
```

The create-many test itself proves an indicator group may be empty because no entry targets any additional configured item and persistence still succeeds.

- [ ] **Step 2: Run service tests and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
```

Expected: new grouped-assignment tests fail while existing legacy tests continue to pass.

- [ ] **Step 3: Add rules and load indicator items with allowed criteria**

Add to `SupportScoreRules::validation()`:

```php
'support_list.*.activity_entries.*.support_indicator_item_id' => ['nullable', 'integer'],
```

In `SupportScoreService`, eager-load ordered indicator items:

```php
$allowedCriteria = SupportCriteria::query()
    ->with('indicatorItems:id,support_criteria_id,sequence,code,description')
    ->whereHas('evaluationList', function ($query) use ($criteriaVersionId) {
        $query->where('criteria_version_id', $criteriaVersionId);
    })
    ->get()
    ->keyBy('id');
```

- [ ] **Step 4: Enforce assignment ownership and immutability**

Before persisting each criterion's entries, normalize and validate:

```php
$indicatorItemId = filled($entryData['support_indicator_item_id'] ?? null)
    ? (int) $entryData['support_indicator_item_id']
    : null;

if ($criterion->group_activity_entries_by_indicator) {
    if (! $indicatorItemId || ! $criterion->indicatorItems->contains('id', $indicatorItemId)) {
        throw ValidationException::withMessages([
            "support_list.{$itemIndex}.activity_entries.{$entryIndex}.support_indicator_item_id" => [
                'กรุณาเลือกตัวชี้วัดย่อยที่อยู่ในเกณฑ์นี้',
            ],
        ]);
    }
} elseif ($indicatorItemId !== null) {
    throw ValidationException::withMessages([
        "support_list.{$itemIndex}.activity_entries.{$entryIndex}.support_indicator_item_id" => [
            'เกณฑ์นี้ไม่ได้แบ่งโครงการตามตัวชี้วัดย่อย',
        ],
    ]);
}
```

Create new entries with the normalized ID. For existing evaluatee or reviewer entries, compare the submitted ID with `$entry->support_indicator_item_id`; reject changes under the nested field key. Keep flat global sequence so reviewer order protection remains deterministic.

- [ ] **Step 5: Run service tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: grouped and legacy persistence tests pass; transaction rollback tests remain green.

- [ ] **Step 6: Commit the persistence slice**

```powershell
git add -- app/Support/SupportScoreRules.php app/Services/SupportScoreService.php app/Services/SupportActivityEntryService.php app/Models/SupportActivityEntry.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
git diff --cached --check
git commit -m "feat: assign support projects to indicator items"
```

---

### Task 5: Expose grouped indicators through the report read model

**Files:**
- Modify: `app/Support/SupportCriteriaReadModel.php`
- Test: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`

**Interfaces:**
- Consumes: grouped schema and persisted entry assignments.
- Produces per criterion: `group_activity_entries_by_indicator`, ordered `indicator_items`, and flat `activity_entries.*.support_indicator_item_id`.

- [ ] **Step 1: Write a failing grouped read-model test**

Create a grouped criterion, two ordered indicator items, and projects in both groups, then assert:

```php
$item = app(SupportCriteriaReadModel::class)->forReport($report)[$evaluationList->id][0];

$this->assertTrue($item['group_activity_entries_by_indicator']);
$this->assertNull($item['indicator']);
$this->assertSame(['2.1', '2.2'], array_column($item['indicator_items'], 'code'));
$this->assertSame(
    [$firstIndicator->id, $firstIndicator->id, $secondIndicator->id],
    array_column($item['activity_entries'], 'support_indicator_item_id')
);
```

- [ ] **Step 2: Run the read-model test and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: FAIL because grouped fields are absent.

- [ ] **Step 3: Load and map grouped data without N+1 queries**

Change the criteria query to eager-load ordered items:

```php
$criteria = SupportCriteria::query()
    ->with('indicatorItems:id,support_criteria_id,sequence,code,description')
    ->whereHas('evaluationList', function ($query) use ($criteriaVersionId) {
        $query->where('criteria_version_id', $criteriaVersionId);
    })
    ->orderBy('evaluation_list_id')
    ->orderBy('sequence')
    ->get();
```

Add to each criterion array:

```php
'group_activity_entries_by_indicator' => (bool) $criterion->group_activity_entries_by_indicator,
'indicator_items' => $criterion->indicatorItems->map(fn ($item) => [
    'id' => $item->id,
    'sequence' => $item->sequence,
    'code' => $item->code,
    'description' => $item->description,
])->values()->all(),
```

Add to each activity entry:

```php
'support_indicator_item_id' => $entry->support_indicator_item_id,
```

- [ ] **Step 4: Run read-model tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: grouped output and all legacy output tests pass.

- [ ] **Step 5: Commit the read-model slice**

```powershell
git add -- app/Support/SupportCriteriaReadModel.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
git diff --cached --check
git commit -m "feat: expose grouped support projects"
```

---

### Task 6: Render grouped indicator projects in the shared evaluation UI

**Files:**
- Create: `resources/views/components/support-indicator-display.blade.php`
- Modify: `resources/views/components/support-activity-display.blade.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `resources/js/support-activity-entries.js`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Test: `tests/js/support-activity-entries.test.mjs`

**Interfaces:**
- Consumes: read-model fields from Task 5.
- Produces grouped DOM contracts `data-support-activity-group`, `data-support-indicator-item-id`, and hidden `data-support-activity-indicator-id` inputs while keeping flat `support_list[criterion][activity_entries][index]` names.

- [ ] **Step 1: Write failing Blade and JavaScript tests**

Add a grouped fixture with indicator items 2.1/2.2 and three assigned projects. Assert the rendered table:

```php
expect($html)
    ->toContain('data-support-activity-group="11"')
    ->toContain('data-support-activity-group="12"')
    ->toContain('+ เพิ่มโครงการในข้อ 2.1')
    ->toContain('+ เพิ่มโครงการในข้อ 2.2')
    ->toContain('support_list[7][activity_entries][0][support_indicator_item_id]')
    ->toContain('value="11"')
    ->toContain('<strong>ดำเนินการวิจัย</strong>')
    ->not->toContain('ส่งตรงเวลา');
```

Add Node assertions that `activityEntryFieldName(7, 0, 'support_indicator_item_id')` returns the exact nested name and that `groupActivityEntries(indicatorItems, entries)` returns two ordered groups without duplicating entries.

- [ ] **Step 2: Run UI tests and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaEvaluationViewTest.php
npm.cmd run test:js
```

Expected: grouped markup and helper assertions fail.

- [ ] **Step 3: Render legacy or grouped indicator content through one component**

Create `support-indicator-display.blade.php`:

```blade
@props(['item'])

@if (!empty($item['group_activity_entries_by_indicator']))
    <ol class="space-y-3">
        @foreach ($item['indicator_items'] ?? [] as $indicatorItem)
            <li class="break-words">
                <span class="font-semibold text-amber-900">{{ $indicatorItem['code'] }}</span>
                <div class="support-criteria-rich-text mt-1">
                    {!! \App\Support\SafeHtml::richText($indicatorItem['description'] ?? '') !!}
                </div>
            </li>
        @endforeach
    </ol>
@else
    <div class="support-criteria-rich-text break-words leading-6">
        {!! \App\Support\SafeHtml::richText($item['indicator'] ?? '') !!}
    </div>
@endif
```

Use it in both desktop and mobile indicator cells. Update the activity display to group saved projects by `support_indicator_item_id` when grouped, while retaining the current flat ordered list for legacy criteria.

- [ ] **Step 4: Render grouped Modal editors and preserve reviewer permissions**

For grouped criteria, loop `indicator_items`, filter the flat `activity_entries`, and render:

```blade
<section data-support-activity-group="{{ $indicatorItem['id'] }}">
    <h6>{{ $indicatorItem['code'] }}</h6>
    <div class="support-criteria-rich-text">
        {!! \App\Support\SafeHtml::richText($indicatorItem['description'] ?? '') !!}
    </div>
    @if ($canEditActivities && $activityEntryRole === 'evaluatee')
        <button type="button"
            data-add-support-activity="{{ $item['id'] }}"
            data-support-indicator-item-id="{{ $indicatorItem['id'] }}">
            + เพิ่มโครงการในข้อ {{ $indicatorItem['code'] }}
        </button>
    @endif
    <div data-support-activity-container>
        @foreach (($item['activity_entries'] ?? []) as $entryIndex => $entry)
            @continue((int) ($entry['support_indicator_item_id'] ?? 0) !== (int) $indicatorItem['id'])
            <article data-support-activity-entry data-support-activity-entry-id="{{ $entry['id'] }}">
                <input type="hidden" data-support-activity-id value="{{ $entry['id'] }}">
                <input type="hidden" data-support-activity-indicator-id
                    value="{{ $entry['support_indicator_item_id'] }}">
                <textarea data-support-activity-content
                    data-original-content="{{ (string) \App\Support\SafeHtml::richText($entry['content'] ?? '') }}">{{ (string) \App\Support\SafeHtml::richText($entry['content'] ?? '') }}</textarea>
            </article>
        @endforeach
    </div>
    <p data-support-activity-empty>ยังไม่มีโครงการ</p>
</section>
```

Each grouped entry includes:

```blade
<input type="hidden"
    name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][support_indicator_item_id]"
    value="{{ $entry['support_indicator_item_id'] }}"
    data-support-activity-indicator-id>
```

Reviewer markup renders this hidden value unchanged and exposes no add, delete, move, or reorder controls.

- [ ] **Step 5: Update client-side create, snapshot, reindex, and display behavior**

Extend the helper module:

```javascript
export function groupActivityEntries(indicatorItems, entries) {
    return indicatorItems.map((item) => ({
        ...item,
        activity_entries: entries.filter(
            (entry) => Number(entry.support_indicator_item_id) === Number(item.id),
        ),
    }));
}
```

Change `createActivityEntryRow(indicatorItemId = '')` to add a hidden input with `data-support-activity-indicator-id`. On add, pass `addActivityButton.dataset.supportIndicatorItemId` and append to that button's group container.

Reindex all entry rows globally and set all four names:

```javascript
if (indicatorId) {
    indicatorId.name = activityTools().activityEntryFieldName(
        criterionId,
        index,
        'support_indicator_item_id',
    );
}
```

Toggle each group's `data-support-activity-empty` based only on rows inside that group. Snapshot and restore the complete `data-support-activity-section`, not only the first container, so cancel/Escape restores every group. Update the table preview with safe `textContent`, grouping projects under the indicator code without injecting unsanitized HTML.

- [ ] **Step 6: Run UI tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php
npm.cmd run test:js
php artisan view:clear
php artisan view:cache
```

Expected: grouped and legacy UI tests pass, JS tests pass, and Blade cache succeeds.

- [ ] **Step 7: Commit the shared UI slice**

```powershell
git add -- resources/views/components/support-indicator-display.blade.php resources/views/components/support-activity-display.blade.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php resources/js/support-activity-entries.js tests/Feature/SupportCriteriaEvaluationViewTest.php tests/js/support-activity-entries.test.mjs
git diff --cached --check
git commit -m "feat: group support projects by indicator"
```

---

### Task 7: Cover route-level role flow and complete verification

**Files:**
- Modify: `tests/Feature/Evaluation/EvaluateeTest.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Consumes: all grouped Admin, persistence, read-model, and UI contracts.
- Produces: end-to-end evidence that grouped projects work through existing evaluatee/evaluator/director/manager routes without changing report state transitions.

- [ ] **Step 1: Add route-level regression tests**

Add an evaluatee test that posts three projects split 2+1 across two items, leaves a third indicator empty, saves Draft, then submits Pending and asserts all assignments remain. Add invalid payload cases for a missing item ID and an item from another criterion/version.

Extend the existing reviewer-role dataset so evaluator, director, and manager submit an unchanged `support_indicator_item_id`, edit content with a reason, and create history. Add a tampered item-ID case that must return validation errors and leave entry/history/comment/report status unchanged.

Add an Admin update test that creates an assigned project and then attempts to remove its indicator item or disable grouped mode; both requests must return 422 and preserve the rows.

- [ ] **Step 2: Run route-level regression tests**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: all tests pass. The evaluatee, evaluator, director, and manager controllers already merge `SupportScoreRules::validation()`, so no controller change belongs in this task. Any failure here must be fixed in the shared rules/service/read-model implementation from Tasks 4-5.

- [ ] **Step 3: Run focused integration and static verification**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
npm.cmd run test:js
npm.cmd run build
php artisan view:clear
php artisan view:cache
git diff --check
```

Expected: every command exits 0; no grouped or legacy test fails.

- [ ] **Step 4: Apply and verify the local migration**

Run:

```powershell
php artisan migrate --force
php artisan migrate:status
```

Expected: `2026_07_21_000002_create_support_indicator_items` is `Ran`. Inspect MySQL foreign keys with:

```powershell
php artisan tinker --execute="dump(Schema::getForeignKeys('support_activity_entries'));"
```

Expected: `support_activity_entry_indicator_fk` references `support_indicator_items.id` with `restrict` deletion.

- [ ] **Step 5: Run the full PHP suite using the repository test database**

Preserve `database/testing.sqlite`, run `vendor\bin\pest.bat --compact`, restore the file, and require exit code 0. Expected: all tests pass with zero failures.

- [ ] **Step 6: Commit the route-level regression tests**

```powershell
git add -- tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git diff --cached --check
git commit -m "test: cover grouped support project flow"
```
