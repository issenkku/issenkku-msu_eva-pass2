# Support Indicator Mode Exclusivity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ทำให้โหมด “แยกโครงการตามตัวชี้วัดย่อย” และ “อนุญาตให้ผู้ถูกประเมินกรอกตัวชี้วัด” เลือกพร้อมกันไม่ได้ พร้อมอนุญาตให้ปิดโหมดแยกโดยรักษาโครงการและความสัมพันธ์เดิม

**Architecture:** UI ของหน้าสร้างและแก้ไขจะ normalize checkbox ให้เป็น mutually exclusive ส่วน `ReportStructureController` บังคับ invariant เดียวกันสำหรับทุก client บริการโครงสร้างเกณฑ์จะรักษาข้อย่อยเดิมเมื่อปิดโหมดและป้องกันการเปิดกลับหากมีโครงการที่ยังไม่สังกัดข้อย่อย ขณะที่บริการบันทึกโครงการจะยอมรับ hidden association เฉพาะแถวเดิมที่ไม่ถูกเปลี่ยน

**Tech Stack:** Laravel 11, PHP 8.2+, Blade/JavaScript, Eloquent, Pest/PHPUnit

## Global Constraints

- ไม่ลบหรือแก้ไฟล์งานอื่นที่ค้างอยู่ใน worktree
- ไม่ลบ `support_indicator_items`, โครงการ, หลักฐาน, ประวัติ หรือ `support_indicator_item_id` เดิมเมื่อปิดโหมดแยก
- โครงการใหม่ในโหมดไม่แยกต้องเก็บ `support_indicator_item_id = null`
- ยังคงห้ามลบตัวชี้วัดย่อยที่มีโครงการอ้างอิง
- ไม่เลือกตัวชี้วัดย่อยให้โครงการโดยอัตโนมัติ
- ใช้ TDD: ต้องเห็น test ใหม่ fail ด้วยสาเหตุที่คาดไว้ก่อนแก้ production code
- `CONTEXT.md` ที่ AGENTS.md อ้างถึงไม่มีอยู่ใน repository; ใช้ design spec และโค้ดปัจจุบันเป็นแหล่งอ้างอิง

---

## File Map

- `resources/views/criteria_config/partials/create-script.blade.php` — จัดสถานะ checkbox และส่วนแสดงผลในหน้าสร้าง
- `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php` — ใช้กติกา checkbox เดียวกันในหน้าแก้ไขและ normalize ข้อมูลเดิม
- `resources/js/support-indicator-mode.js` — pure state resolver ที่เป็นแหล่งกติกา mutual exclusion กลาง
- `resources/js/app.ts` — โหลด state resolver และเปิด API ให้ inline criteria scripts ใช้
- `tests/js/support-indicator-mode.test.mjs` — behavioral test ของทุกสถานะ checkbox
- `app/Http/Controllers/ReportStructureController.php` — ตรวจ invariant ของ payload ทั้ง create/update
- `app/Services/SupportIndicatorItemService.php` — นโยบายปิด/เปิดโหมดและการรักษาข้อย่อย
- `tests/Feature/Report/SupportCriteriaTemplateTest.php` — feature test ของการตั้งค่า การเปลี่ยนโหมด และการรักษาข้อมูล
- `app/Services/SupportActivityEntryService.php` — ตรวจ hidden association ของโครงการเดิมในโหมดไม่แยก
- `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php` — service test สำหรับการบันทึกโครงการเดิม/ใหม่และการป้องกันการปลอม association

---

### Task 1: ทำให้ checkbox สองโหมดเป็น mutually exclusive

**Files:**
- Create: `tests/js/support-indicator-mode.test.mjs`
- Create: `resources/js/support-indicator-mode.js`
- Modify: `resources/js/app.ts`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`

**Interfaces:**
- Consumes: `.support_allow_activity_entries`, `.support_allow_evaluatee_indicator`, `.support_group_by_indicator`
- Produces: `resolveSupportIndicatorMode({ allowActivities, allowEvaluateeIndicator, grouped })` และ `window.SupportIndicatorMode.resolveSupportIndicatorMode`

- [ ] **Step 1: เพิ่ม behavioral test ที่ต้อง fail ก่อน**

สร้าง `tests/js/support-indicator-mode.test.mjs`:

```javascript
import test from 'node:test';
import assert from 'node:assert/strict';

import { resolveSupportIndicatorMode } from '../../resources/js/support-indicator-mode.js';

test('disables and clears indicator modes when activity entries are disabled', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: false,
            allowEvaluateeIndicator: true,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: false,
            groupedDisabled: true,
        },
    );
});

test('evaluatee-owned indicator mode wins over conflicting grouped data', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: true,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: true,
            allowEvaluateeIndicatorDisabled: false,
            groupedChecked: false,
            groupedDisabled: true,
        },
    );
});

test('grouped mode prevents enabling evaluatee-owned indicators', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: false,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: true,
            groupedDisabled: false,
        },
    );
});

test('leaves both choices enabled when neither exclusive mode is selected', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: false,
            grouped: false,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: false,
            groupedChecked: false,
            groupedDisabled: false,
        },
    );
});
```

- [ ] **Step 2: รัน test เพื่อยืนยัน RED**

Run:

```powershell
node --test tests/js/support-indicator-mode.test.mjs
```

Expected: FAIL ด้วย `ERR_MODULE_NOT_FOUND` เพราะ state resolver ยังไม่มี

- [ ] **Step 3: สร้าง state resolver และเปิดผ่าน app bundle**

สร้าง `resources/js/support-indicator-mode.js`:

```javascript
export function resolveSupportIndicatorMode({ allowActivities, allowEvaluateeIndicator, grouped }) {
    if (!allowActivities) {
        return {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: false,
            groupedDisabled: true,
        };
    }

    const evaluateeOwnsIndicator = Boolean(allowEvaluateeIndicator);
    const groupedMode = Boolean(grouped) && !evaluateeOwnsIndicator;

    return {
        allowEvaluateeIndicatorChecked: evaluateeOwnsIndicator,
        allowEvaluateeIndicatorDisabled: groupedMode,
        groupedChecked: groupedMode,
        groupedDisabled: evaluateeOwnsIndicator,
    };
}

if (typeof window !== 'undefined') {
    window.SupportIndicatorMode = { resolveSupportIndicatorMode };
}
```

เพิ่มใน `resources/js/app.ts`:

```typescript
import './support-indicator-mode';
```

- [ ] **Step 4: ใช้ resolver ใน `toggleSupportIndicatorMode` ทั้งสองไฟล์**

แทน logic checked/disabled เดิมด้วย:

```javascript
const mode = window.SupportIndicatorMode.resolveSupportIndicatorMode({
    allowActivities: allow.checked,
    allowEvaluateeIndicator: allowEvaluateeIndicator?.checked || false,
    grouped: grouped.checked,
});

grouped.checked = mode.groupedChecked;
grouped.disabled = mode.groupedDisabled;
if (allowEvaluateeIndicator) {
    allowEvaluateeIndicator.checked = mode.allowEvaluateeIndicatorChecked;
    allowEvaluateeIndicator.disabled = mode.allowEvaluateeIndicatorDisabled;
}
if (!allow.checked && allowEvaluateeWeight) {
    allowEvaluateeWeight.checked = false;
}
if (allowEvaluateeWeight) {
    allowEvaluateeWeight.disabled = !allow.checked;
}

const evaluateeOwnsIndicator = mode.allowEvaluateeIndicatorChecked;
```

คง logic ล้าง rich text, น้ำหนัก และการซ่อน `[data-support-legacy-indicator]`/`.support_indicator_items` เดิมไว้

- [ ] **Step 5: รัน tests เพื่อยืนยัน GREEN**

Run:

```powershell
node --test tests/js/support-indicator-mode.test.mjs
php vendor/bin/pest tests/Feature/SupportCriteriaTemplateViewTest.php
```

Expected: PASS ทั้งสองคำสั่ง

- [ ] **Step 6: Commit**

```powershell
git add -- tests/js/support-indicator-mode.test.mjs resources/js/support-indicator-mode.js resources/js/app.ts resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php
git commit -m "fix: make support indicator modes exclusive"
```

---

### Task 2: บังคับ mutual exclusion ฝั่ง Server

**Files:**
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`

**Interfaces:**
- Consumes: `validateSupportIndicatorConfiguration(array $categories): void`
- Produces: validation error key `categories.{category}.evaluation_lists.{list}.support_criterias.{criterion}.group_activity_entries_by_indicator`

- [ ] **Step 1: เพิ่ม failing feature tests สำหรับ create และ update**

ใน `SupportCriteriaTemplateTest` เพิ่ม test ที่สร้าง payload ซึ่งมีสองค่าเป็นจริง:

```php
public function test_evaluatee_indicator_and_grouped_indicator_modes_are_mutually_exclusive(): void
{
    $invalidCriterion = [
        'sequence' => 1,
        'activity_name' => '<p>งานวิจัย</p>',
        'indicator' => null,
        'target_value' => 100,
        'weight' => 100,
        'allow_activity_entries' => true,
        'allow_evaluatee_indicator' => true,
        'group_activity_entries_by_indicator' => true,
        'indicator_items' => [
            ['sequence' => 1, 'code' => '2.1'],
        ],
    ];

    $this->postJson(route('report-structure.store'), $this->payload([$invalidCriterion]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'categories.0.evaluation_lists.0.support_criterias.0.group_activity_entries_by_indicator'
        );

    $created = $this->postJson(route('report-structure.store'), $this->payload([[
        ...$invalidCriterion,
        'allow_evaluatee_indicator' => false,
    ]]))->assertCreated();

    $version = CriteriaVersion::with([
        'reportDatas',
        'categories.evaluationLists.supportCriterias.indicatorItems',
    ])->findOrFail($created->json('data.id'));
    $category = $version->categories->first();
    $evaluationList = $category->evaluationLists->first();
    $criterion = $evaluationList->supportCriterias->first();
    $item = $criterion->indicatorItems->first();

    $update = $this->payload([[
        ...$invalidCriterion,
        'support_criteria_id' => $criterion->id,
        'indicator_items' => [[
            'support_indicator_item_id' => $item->id,
            'sequence' => 1,
            'code' => $item->code,
        ]],
    ]]);
    $update['version_name'] = $version->version_name;
    $update['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
    $update['categories'][0]['categorie_id'] = $category->id;
    $update['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

    $this->putJson(route('report-structure.update', $version->id), $update)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'categories.0.evaluation_lists.0.support_criterias.0.group_activity_entries_by_indicator'
        );
}
```

- [ ] **Step 2: รัน test เพื่อยืนยัน RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="mutually_exclusive"
```

Expected: FAIL เพราะ create/update ปัจจุบันยอมรับสองโหมดพร้อมกัน

- [ ] **Step 3: เพิ่ม validation กลาง**

ใน `validateSupportIndicatorConfiguration()` หลังอ่าน `$grouped` และ `$allowEvaluateeIndicator`:

```php
if ($grouped && $allowEvaluateeIndicator) {
    $errors["{$base}.group_activity_entries_by_indicator"][] =
        'ไม่สามารถแยกโครงการตามตัวชี้วัดย่อยพร้อมกับให้ผู้ถูกประเมินกรอกตัวชี้วัดได้';
}
```

- [ ] **Step 4: รัน test เพื่อยืนยัน GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="mutually_exclusive"
```

Expected: PASS

- [ ] **Step 5: Commit**

```powershell
git add -- app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "fix: reject conflicting support indicator modes"
```

---

### Task 3: อนุญาตให้ปิดโหมดแยกโดยรักษาข้อมูล และควบคุมการเปิดกลับ

**Files:**
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`
- Modify: `app/Services/SupportIndicatorItemService.php`

**Interfaces:**
- Consumes: `SupportIndicatorItemService::sync(SupportCriteria $criterion, bool $grouped, array $items, bool $wasGrouped): void`
- Produces: ปิดโหมดได้โดยไม่ลบข้อมูล; เปิดกลับไม่ได้เมื่อ `activityEntries()->whereNull('support_indicator_item_id')` มีข้อมูล

- [ ] **Step 1: แยก test เดิมและเขียน expectation ใหม่**

เปลี่ยน `test_admin_cannot_remove_or_disable_grouped_indicator_items_with_projects` ให้เหลือการยืนยันว่า “ลบข้อย่อยที่ถูกอ้างอิงไม่ได้” และเพิ่ม test ใหม่ `test_admin_can_disable_grouping_without_deleting_projects_or_indicator_links` โดยใช้ setup เดิม จากนั้นส่ง update:

```php
$evidence = $entry->evidenceAnswers()->create([
    'evaluation_list_id' => $criterion->evaluation_list_id,
    'support_criteria_id' => $criterion->id,
    'report_id' => $report->id,
    'link' => 'https://example.com/project-proof',
]);

$disablePayload['categories'][0]['evaluation_lists'][0]['support_criterias'][0] = [
    'support_criteria_id' => $criterion->id,
    'sequence' => 1,
    'activity_name' => $criterion->activity_name,
    'indicator' => null,
    'target_value' => $criterion->target_value,
    'weight' => $criterion->weight,
    'allow_activity_entries' => true,
    'allow_evaluatee_indicator' => true,
    'group_activity_entries_by_indicator' => false,
    'indicator_items' => [],
];

$this->putJson(route('report-structure.update', $version->id), $disablePayload)
    ->assertOk();

$this->assertDatabaseHas('support_criterias', [
    'id' => $criterion->id,
    'allow_evaluatee_indicator' => true,
    'group_activity_entries_by_indicator' => false,
]);
$this->assertDatabaseHas('support_indicator_items', ['id' => $first->id]);
$this->assertDatabaseHas('support_activity_entries', [
    'id' => $entry->id,
    'support_indicator_item_id' => $first->id,
]);
$this->assertDatabaseHas('evidence_answers', [
    'id' => $evidence->id,
    'support_activity_entry_id' => $entry->id,
    'link' => 'https://example.com/project-proof',
]);
```

เพิ่ม test `test_admin_cannot_enable_grouping_while_a_project_has_no_indicator_item`:

```php
SupportActivityEntry::create([
    'report_id' => $report->id,
    'support_criteria_id' => $criterion->id,
    'support_indicator_item_id' => null,
    'sequence' => 1,
    'content' => '<p>โครงการที่ยังไม่สังกัดข้อย่อย</p>',
]);

$this->putJson(route('report-structure.update', $version->id), $enablePayload)
    ->assertUnprocessable()
    ->assertJsonValidationErrors('support_criterias');
```

`$enablePayload` ต้องมี `group_activity_entries_by_indicator = true`, `allow_evaluatee_indicator = false` และส่ง indicator item เดิมพร้อม ID

- [ ] **Step 2: รัน tests เพื่อยืนยัน RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="disable_grouping|enable_grouping"
```

Expected:
- disable test FAIL ด้วย validation “ไม่สามารถปิดการแบ่ง…”
- enable test FAIL เพราะระบบยังเปิดโหมดได้ทั้งที่มีโครงการ `null`

- [ ] **Step 3: แก้ `SupportIndicatorItemService::sync`**

แทน block ที่ปฏิเสธการปิดด้วย early return:

```php
if (! $grouped) {
    return;
}
```

ก่อนเริ่ม sync `$items` เพิ่ม guard สำหรับการเปิดกลับ:

```php
if (! $wasGrouped
    && $criterion->activityEntries()
        ->whereNull('support_indicator_item_id')
        ->exists()) {
    throw ValidationException::withMessages([
        'support_criterias' => [
            'ไม่สามารถเปิดการแบ่งตามตัวชี้วัดย่อยได้ เนื่องจากมีโครงการที่ยังไม่ได้สังกัดตัวชี้วัดย่อย',
        ],
    ]);
}
```

คง block `$removed->contains(...)` เดิมไว้เพื่อห้ามลบข้อย่อยที่ถูกอ้างอิง

- [ ] **Step 4: รัน tests เพื่อยืนยัน GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="grouping|grouped_indicator_items_with_projects"
```

Expected: PASS รวมทั้ง regression ที่ยังห้ามลบข้อย่อย

- [ ] **Step 5: Commit**

```powershell
git add -- app/Services/SupportIndicatorItemService.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "fix: preserve support projects when disabling groups"
```

---

### Task 4: อนุญาต hidden association เฉพาะโครงการเดิมในโหมดไม่แยก

**Files:**
- Modify: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Modify: `app/Services/SupportActivityEntryService.php`

**Interfaces:**
- Consumes: รายการ `SupportActivityEntry` ของ report/criterion ปัจจุบันและ payload `id`, `support_indicator_item_id`
- Produces: `validateIndicatorAssignment(..., Collection $existingEntriesById): ?int` ที่ยอมรับ ID เดิมแบบ unchanged เท่านั้น

- [ ] **Step 1: เพิ่ม failing service test สำหรับการเก็บ association เดิม**

```php
public function test_ungrouped_project_can_keep_its_existing_hidden_indicator_link(): void
{
    $item = $this->criterion->indicatorItems()->create([
        'sequence' => 1,
        'code' => '2.1',
    ]);
    $entry = SupportActivityEntry::create([
        'report_id' => $this->report->id,
        'support_criteria_id' => $this->criterion->id,
        'support_indicator_item_id' => $item->id,
        'sequence' => 1,
        'content' => '<p>โครงการเดิม</p>',
    ]);
    $this->criterion->update([
        'indicator' => null,
        'allow_evaluatee_indicator' => true,
        'group_activity_entries_by_indicator' => false,
    ]);

    $this->persist([[
        'id' => $entry->id,
        'support_indicator_item_id' => $item->id,
        'content' => '<p>โครงการเดิมที่แก้ไขแล้ว</p>',
        'indicator' => '<p>ตัวชี้วัดที่ผู้ถูกประเมินกรอก</p>',
    ]]);

    $this->assertDatabaseHas('support_activity_entries', [
        'id' => $entry->id,
        'support_indicator_item_id' => $item->id,
        'indicator' => '<p>ตัวชี้วัดที่ผู้ถูกประเมินกรอก</p>',
    ]);
}
```

เพิ่ม test ป้องกันการปลอมและโครงการใหม่:

```php
public function test_ungrouped_mode_rejects_changed_or_new_indicator_links(): void
{
    $first = $this->criterion->indicatorItems()->create(['sequence' => 1, 'code' => '2.1']);
    $second = $this->criterion->indicatorItems()->create(['sequence' => 2, 'code' => '2.2']);
    $entry = SupportActivityEntry::create([
        'report_id' => $this->report->id,
        'support_criteria_id' => $this->criterion->id,
        'support_indicator_item_id' => $first->id,
        'sequence' => 1,
        'content' => '<p>โครงการเดิม</p>',
    ]);
    $this->criterion->update([
        'indicator' => null,
        'allow_evaluatee_indicator' => true,
        'group_activity_entries_by_indicator' => false,
    ]);

    foreach ([
        [
            'id' => $entry->id,
            'support_indicator_item_id' => $second->id,
            'content' => '<p>เปลี่ยนข้อย่อย</p>',
            'indicator' => '<p>ตัวชี้วัด</p>',
        ],
        [
            'support_indicator_item_id' => $first->id,
            'content' => '<p>โครงการใหม่</p>',
            'indicator' => '<p>ตัวชี้วัด</p>',
        ],
    ] as $payload) {
        try {
            $this->persist([$payload]);
            $this->fail('Expected hidden indicator validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'support_list.0.activity_entries.0.support_indicator_item_id',
                $exception->errors()
            );
        }
    }
}
```

- [ ] **Step 2: รัน tests เพื่อยืนยัน RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php --filter="hidden_indicator|indicator_links"
```

Expected: test แรก FAIL เพราะโหมดไม่แยกปฏิเสธ non-null ID; test ป้องกันยังผ่าน

- [ ] **Step 3: โหลด existing entries ก่อน normalize payload**

ใน `persist()` ย้าย query `SupportActivityEntry::query()->where(...)->get()` ให้เกิดก่อน loop ที่เรียก `validateIndicatorAssignment()` แล้วสร้าง:

```php
$existingById = $existingEntries->keyBy('id');
```

ส่ง `$existingById` เป็น argument สุดท้ายเข้า `validateIndicatorAssignment()`

- [ ] **Step 4: ปรับ signature และกติกาโหมดไม่แยก**

```php
private function validateIndicatorAssignment(
    SupportCriteria $criterion,
    array $entryData,
    int|string $itemIndex,
    int $entryIndex,
    Collection $existingById
): ?int {
    // คง grouped-mode validation เดิม

    if ($indicatorItemId === null) {
        return null;
    }

    $entryId = filled($entryData['id'] ?? null)
        ? (int) $entryData['id']
        : null;
    /** @var SupportActivityEntry|null $existing */
    $existing = $entryId ? $existingById->get($entryId) : null;

    if (! $existing
        || (int) $existing->support_indicator_item_id !== $indicatorItemId
        || ! $criterion->indicatorItems->contains('id', $indicatorItemId)) {
        throw ValidationException::withMessages([
            $errorKey => [
                'โหมดนี้อนุญาตให้คงตัวชี้วัดย่อยเดิมของโครงการที่มีอยู่เท่านั้น',
            ],
        ]);
    }

    return $indicatorItemId;
}
```

คงการตรวจซ้ำใน `persistEvaluateeChanges()` และ `persistReviewerChanges()` เพื่อป้องกันการย้ายโครงการตามเดิม

- [ ] **Step 5: รัน service tests เพื่อยืนยัน GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
```

Expected: PASS ทั้งไฟล์

- [ ] **Step 6: Commit**

```powershell
git add -- app/Services/SupportActivityEntryService.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
git commit -m "fix: retain legacy support indicator links"
```

---

### Task 5: ตรวจ regression ทั้ง flow

**Files:**
- Verify only; ไม่แก้ production code เว้นแต่ test ชี้ regression ที่เกิดจากงานนี้

**Interfaces:**
- Consumes: ผลงาน Tasks 1–4
- Produces: หลักฐานว่าการตั้งค่า การบันทึกโครงการ และ JavaScript contracts ผ่านพร้อมกัน

- [ ] **Step 1: รัน targeted suites**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaTemplateViewTest.php
php vendor/bin/pest tests/Feature/Report/SupportCriteriaTemplateTest.php
php vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
node --test tests/js/support-indicator-mode.test.mjs
```

Expected: PASS ทั้งห้าคำสั่ง ไม่มี warning/error ใหม่ โดย view suite เดิมยืนยันว่าโหมดไม่แยกแสดงโครงการเป็นรายการรวม

- [ ] **Step 2: รันชุดทดสอบทั้งหมด**

```powershell
composer test
npm run test:js
```

Expected: PASS ทั้ง PHP และ JavaScript suites

- [ ] **Step 3: ตรวจ diff และสถานะ repository**

```powershell
git diff --check
git status --short
git log -5 --oneline
```

Expected:
- `git diff --check` ไม่มี output
- ไม่มี source/test file ของงานนี้ค้างโดยไม่ commit
- ไฟล์งานเดิมของผู้ใช้ยังอยู่และไม่ถูกรวมใน commits ของงานนี้

- [ ] **Step 4: ขอ code review ก่อนสรุป**

ใช้ `superpowers:requesting-code-review` ตรวจเทียบ implementation กับ:

```text
docs/superpowers/specs/2026-07-27-support-indicator-mode-exclusivity-design.md
docs/superpowers/plans/2026-07-27-support-indicator-mode-exclusivity.md
```

แก้เฉพาะ finding ที่ตรวจยืนยันแล้วว่าอยู่ในขอบเขต จากนั้นรัน targeted suites ซ้ำ
