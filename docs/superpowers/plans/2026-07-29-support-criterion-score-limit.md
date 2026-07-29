# Support Criterion Score Limit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** จำกัดคะแนนที่ได้ระดับเกณฑ์สายสนับสนุนให้เป็นจำนวนเต็ม `1–5` และไม่เกินระดับค่าเป้าหมาย โดยตรวจทั้ง Browser และ Server

**Architecture:** เพิ่ม pure JavaScript validator ในโมดูลคำนวณคะแนนเพื่อให้ทดสอบแยกได้ แล้วให้ Blade Modal ใช้ validator เดียวกันกับข้อกำหนดของ input ฝั่ง Server เปลี่ยนกฎพื้นฐานเฉพาะคะแนนระดับเกณฑ์และให้ `SupportScoreService` ตรวจเพดานแบบ dynamic จาก `SupportCriteria::target_value` ก่อนคำนวณหรือบันทึก

**Tech Stack:** Laravel 11, PHP 8.2, Blade, Pest/PHPUnit, JavaScript ES modules, Node.js test runner

## Global Constraints

- เปลี่ยนเฉพาะ `support_list[*][achieved_score]`
- `support_list[*][activity_entries][*][achieved_score]` ยังคงรับ `0–100` และทศนิยมไม่เกินสองตำแหน่ง
- คะแนนระดับเกณฑ์ที่ไม่ว่างต้องเป็นจำนวนเต็ม `1–5` และไม่เกิน `support_criterias.target_value`
- ช่องคะแนนระดับเกณฑ์ยังคง nullable
- ห้ามปรับลดคะแนนผิดเงื่อนไขให้อัตโนมัติ
- ไม่มี data migration สำหรับคะแนนเดิม
- Server ใช้ `SupportCriteria` จากฐานข้อมูลเป็นแหล่งข้อมูลจริงสำหรับเพดานคะแนน
- รักษาการคำนวณคะแนนถ่วงน้ำหนัก ประวัติ เหตุผล และหลักฐานเดิม
- รักษาพฤติกรรมการวางปุ่ม `+ เพิ่มกิจกรรม/โครงการ` ใต้รายการจาก commit `e9a1e20`

---

## File Structure

- `resources/js/support-score-calculator.js` — เพิ่ม pure function สำหรับตรวจคะแนนระดับเกณฑ์และ expose ผ่าน `window.SupportScoreCalculator`
- `tests/js/support-score-calculator.test.mjs` — ทดสอบช่วงจำนวนเต็ม เพดาน 5 เพดานตามเป้าหมาย และ nullable โดยตรง
- `resources/views/components/support-criteria-table.blade.php` — กำหนด input attributes, target data และข้อความกำกับ
- `resources/views/components/support-criteria-table-script.blade.php` — ใช้ pure validator ก่อนบันทึก Modal และคง accessibility flow เดิม
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — ตรวจ contract ของ HTML และการเชื่อม validator โดยรักษาการแก้ไขที่ค้างอยู่ในไฟล์
- `app/Support/SupportScoreRules.php` — ตรวจรูปแบบพื้นฐานของคะแนนระดับเกณฑ์เป็น nullable integer ช่วง `1–5`
- `app/Services/SupportScoreService.php` — ตรวจคะแนนไม่เกิน `target_value` ของเกณฑ์ที่อนุญาตก่อน normalize และบันทึก
- `tests/Feature/Evaluation/SupportScoreServiceTest.php` — ทดสอบ Server validation และปรับกรณีเดิมที่ใช้คะแนนระดับเกณฑ์นอกกติกาใหม่

---

### Task 1: Pure Browser Score Validator

**Files:**

- Modify: `resources/js/support-score-calculator.js`
- Test: `tests/js/support-score-calculator.test.mjs`

**Interfaces:**

- Consumes: string หรือ number `value` และ string หรือ number `targetValue`
- Produces: `isCriterionScoreValid(value, targetValue): boolean`
- Produces: `window.SupportScoreCalculator.isCriterionScoreValid`

- [ ] **Step 1: Write failing JavaScript tests**

เพิ่ม import และ test ต่อไปนี้ใน `tests/js/support-score-calculator.test.mjs`:

```js
import {
    calculateEntryWeightedScore,
    calculateSupportAchievement,
    isCriterionScoreValid,
} from '../../resources/js/support-score-calculator.js';

test('accepts only nullable whole criterion scores from one through five within target', () => {
    assert.equal(isCriterionScoreValid('', 5), true);
    assert.equal(isCriterionScoreValid('1', 5), true);
    assert.equal(isCriterionScoreValid('5', 5), true);
    assert.equal(isCriterionScoreValid('3', 3.5), true);
});

test('rejects criterion scores outside the integer range or above target', () => {
    assert.equal(isCriterionScoreValid('0', 5), false);
    assert.equal(isCriterionScoreValid('6', 6), false);
    assert.equal(isCriterionScoreValid('3.5', 5), false);
    assert.equal(isCriterionScoreValid('4', 3.5), false);
    assert.equal(isCriterionScoreValid('1', 0.5), false);
});
```

จัด import เดิมใหม่ให้เหลือ import block เดียวตามตัวอย่าง

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
node --test tests/js/support-score-calculator.test.mjs
```

Expected: FAIL เพราะ `support-score-calculator.js` ยังไม่ export `isCriterionScoreValid`

- [ ] **Step 3: Implement the minimal pure validator**

เพิ่มใน `resources/js/support-score-calculator.js`:

```js
export function isCriterionScoreValid(value, targetValue) {
    const rawValue = String(value ?? '').trim();
    if (rawValue === '') return true;

    const score = Number(rawValue);
    const target = Number(targetValue);

    return (
        Number.isInteger(score) &&
        score >= 1 &&
        score <= 5 &&
        Number.isFinite(target) &&
        score <= target
    );
}
```

เพิ่ม function ใน browser global:

```js
window.SupportScoreCalculator = {
    calculateEntryWeightedScore,
    calculateSupportAchievement,
    isCriterionScoreValid,
};
```

- [ ] **Step 4: Run the focused test and verify GREEN**

Run:

```powershell
node --test tests/js/support-score-calculator.test.mjs
```

Expected: PASS ทุก test ในไฟล์

- [ ] **Step 5: Commit the pure validator**

```powershell
git add resources/js/support-score-calculator.js tests/js/support-score-calculator.test.mjs
git commit -m "feat: validate support criterion scores in browser"
```

---

### Task 2: Criterion Score Input and Modal Feedback

**Files:**

- Modify: `resources/views/components/support-criteria-table.blade.php:726-741`
- Modify: `resources/views/components/support-criteria-table-script.blade.php:20-40,129-136`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**

- Consumes: `item.target_value` จาก support criteria read model
- Consumes: `window.SupportScoreCalculator.isCriterionScoreValid(value, targetValue)`
- Produces: `data-support-target` ซึ่งมีค่าเท่ากับ `target_value` บน input ระดับเกณฑ์
- Produces: HTML constraints `min="1"`, dynamic `max` เท่ากับ `min(5, target_value)` และ `step="1"`
- Produces: ข้อความกำกับและ Modal error ที่ระบุ `target_value`

- [ ] **Step 1: Write failing Blade contract tests**

ใน `tests/Feature/SupportCriteriaEvaluationViewTest.php` เปลี่ยน `supportViewItem()` ให้ใช้ข้อมูลที่ผ่านกติกาใหม่:

```php
'target_value' => '5.00',
// ...
'achieved_score' => '5.00',
'weighted_score' => '1.00',
```

เพิ่ม test แยกต่อไปนี้:

```php
test('criterion score input accepts whole values from one through its target', function () {
    $html = view('components.support-criteria-table', [
        'items' => [array_replace(supportViewItem(), [
            'target_value' => '4.00',
            'achieved_score' => '4.00',
        ])],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="4" step="1"')
        ->toContain('data-support-target="4.00"')
        ->toContain('กรอกเฉพาะจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย 4.00');
});

test('criterion score input caps its browser maximum at five', function () {
    $html = view('components.support-criteria-table', [
        'items' => [array_replace(supportViewItem(), ['target_value' => '12.00'])],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="5" step="1"')
        ->toContain('data-support-target="12.00"');
});
```

ใน test `shared support script and all role components expose the same contracts` เพิ่ม:

```php
->toContain('isCriterionScoreValid')
->toContain('input.dataset.supportTarget')
->toContain('ต้องเป็นจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย')
```

- [ ] **Step 2: Run the view tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: FAIL เพราะ input ยังใช้ `min="0" step="0.01"` ไม่มี dynamic `max`, target data หรือข้อความใหม่

- [ ] **Step 3: Implement input attributes and help text**

ก่อน `<label>` ของคะแนนระดับเกณฑ์ใน `support-criteria-table.blade.php` คำนวณเพดาน:

```blade
@php
    $criterionScoreMaximum = min(5, (float) $item['target_value']);
@endphp
```

เปลี่ยน input และ help text เป็น:

```blade
<input id="support-score-{{ $item['id'] }}" type="number" min="1"
    max="{{ $criterionScoreMaximum }}" step="1"
    name="support_list[{{ $item['id'] }}][achieved_score]"
    value="{{ $item['achieved_score'] }}"
    aria-describedby="support-score-help-{{ $item['id'] }}"
    data-support-score
    data-support-target="{{ $item['target_value'] }}"
    data-support-id="{{ $item['id'] }}"
    data-support-weight="{{ $item['weight'] }}"
    data-support-original-score="{{ $item['achieved_score'] }}"
    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
    placeholder="1">
<span id="support-score-help-{{ $item['id'] }}"
    class="mt-1 block text-xs font-normal text-slate-500">
    กรอกเฉพาะจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย {{ $item['target_value'] }}
</span>
```

คง block คะแนนรายกิจกรรมใน `support-activity-entry-editor.blade.php` ไว้โดยไม่เปลี่ยน

- [ ] **Step 4: Connect Modal validation to the pure validator**

เพิ่ม wrapper ใกล้ `calculateEntryWeightedScore` ใน `support-criteria-table-script.blade.php`:

```js
const isCriterionScoreValid = (value, targetValue) => {
    const validator = window.SupportScoreCalculator?.isCriterionScoreValid;
    if (validator) return validator(value, targetValue);

    const rawValue = String(value ?? '').trim();
    if (rawValue === '') return true;
    const score = Number(rawValue);
    const target = Number(targetValue);
    return Number.isInteger(score)
        && score >= 1
        && score <= 5
        && Number.isFinite(target)
        && score <= target;
};
```

แทน validation เดิมของ `[data-support-score]` ด้วย:

```js
const targetValue = input?.dataset.supportTarget ?? '';
if (input && !isCriterionScoreValid(value, targetValue)) {
    errors.push(
        `ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย ${targetValue}`,
    );
    rememberInvalid(input);
}
```

อย่าเปลี่ยน `decimalPattern` หรือ validation ของ `[data-support-entry-score]`

- [ ] **Step 5: Run focused Browser and view tests**

Run:

```powershell
node --test tests/js/support-score-calculator.test.mjs
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: ทั้งสองคำสั่ง PASS

- [ ] **Step 6: Review the scoped diff**

Run:

```powershell
git diff -- resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: การวางปุ่ม `+ เพิ่มกิจกรรม/โครงการ` และ assertions ตำแหน่งปุ่มจาก commit `e9a1e20` ยังอยู่ครบ

- [ ] **Step 7: Commit the input and Modal validation**

```powershell
git add resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: constrain support criterion score input"
```

---

### Task 3: Server-Side Criterion Score Enforcement

**Files:**

- Modify: `app/Support/SupportScoreRules.php:16`
- Modify: `app/Services/SupportScoreService.php:151-183`
- Test: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

**Interfaces:**

- Consumes: validated nullable integer `support_list.*.achieved_score`
- Consumes: authoritative `SupportCriteria::$target_value`
- Produces: normalized `?int` criterion score
- Throws: `ValidationException` keyed by `support_list.<index>.achieved_score` when score exceeds target

- [ ] **Step 1: Write failing Server validation tests**

ใน `setUp()` ของ `SupportScoreServiceTest` เปลี่ยนเกณฑ์หลักเป็น:

```php
'target_value' => 5,
```

เพิ่ม tests:

```php
use PHPUnit\Framework\Attributes\DataProvider;

public function test_it_accepts_whole_criterion_scores_within_one_through_five_and_target(): void
{
    foreach ([1, 5] as $score) {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => $score,
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluatee, null, false);

        $this->assertDatabaseHas('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => number_format($score, 2, '.', ''),
        ]);
    }
}

#[DataProvider('invalidCriterionScores')]
public function test_it_rejects_non_integer_criterion_scores_outside_one_through_five(
    int|float|string $score
): void {
    try {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => $score,
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluatee, null, false);
        $this->fail('Expected validation failure');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('support_list.0.achieved_score', $exception->errors());
    }
}

public static function invalidCriterionScores(): array
{
    return [
        'zero' => [0],
        'above five' => [6],
        'decimal' => [3.5],
    ];
}

public function test_it_rejects_a_criterion_score_above_its_target(): void
{
    $this->criterion->update(['target_value' => 3.5]);

    try {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 4,
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluatee, null, false);
        $this->fail('Expected validation failure');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('support_list.0.achieved_score', $exception->errors());
        $this->assertStringContainsString(
            'ต้องไม่เกินระดับค่าเป้าหมาย 3.50',
            $exception->errors()['support_list.0.achieved_score'][0]
        );
    }
}
```

- [ ] **Step 2: Run focused Server tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportScoreServiceTest.php --filter="criterion_score|calculates_and_keeps"
```

Expected: tests ใหม่ FAIL เพราะ `0`, `6`, `3.5` และคะแนนเกิน target ยังผ่านได้

- [ ] **Step 3: Add static request rules**

เปลี่ยนเฉพาะ parent score rule ใน `SupportScoreRules::validation()`:

```php
'support_list.*.achieved_score' => ['nullable', 'integer', 'between:1,5'],
```

คง activity entry rule เดิม:

```php
'support_list.*.activity_entries.*.achieved_score' => ['nullable', 'numeric', 'decimal:0,2'],
```

- [ ] **Step 4: Validate the dynamic target in `SupportScoreService`**

ใน `normalizeAndValidateItems()` หลังตรวจว่า criterion id ได้รับอนุญาต ให้ดึง model และ normalize คะแนน:

```php
/** @var SupportCriteria $criterion */
$criterion = $allowedCriteria->get($criterionId);
$achievedScore = $item['achieved_score'] ?? null;
$normalizedScore = $achievedScore === null || $achievedScore === ''
    ? null
    : (int) $achievedScore;

if ($normalizedScore !== null && $normalizedScore > (float) $criterion->target_value) {
    throw ValidationException::withMessages([
        "support_list.{$index}.achieved_score" => [
            "ค่าคะแนนที่ได้ต้องไม่เกินระดับค่าเป้าหมาย {$criterion->target_value}",
        ],
    ]);
}
```

แล้วกำหนด normalized item ด้วย:

```php
'achieved_score' => $normalizedScore,
```

ลบ normalization เดิมที่ใช้ `round((float) $achievedScore, 2)`

- [ ] **Step 5: Align legacy parent-score tests with the new contract**

แก้เฉพาะคะแนนระดับเกณฑ์ใน `SupportScoreServiceTest`:

- `test_it_calculates_and_keeps_the_uncapped_total_without_deleting_other_evidence`: ใช้คะแนน `5`, expected weighted `1.00`, total `1.0`
- `test_it_persists_the_support_achievement_score_using_five_target_levels`: ใช้คะแนน `4`, expected support total `4.0`, achievement `0.8`
- เปลี่ยน `test_the_persisted_total_is_not_capped_at_one_hundred` เป็น test การปฏิเสธคะแนนเกิน `5` หรือเอาออกหากครอบคลุมด้วย data provider แล้ว
- reviewer history tests ใช้คะแนนเดิม `4`, คะแนนใหม่ `5`, weighted เดิม `0.80`, weighted ใหม่ `1.00`
- rollback test ใช้คะแนนเดิม `4`, คะแนนใหม่ `5` เพื่อให้ failure มาจาก activity validation ตามจุดประสงค์เดิม
- กรณี criterion คนละ versionใช้คะแนน `1` เพื่อให้ failure มาจาก authorization ของ criterion ไม่ใช่ช่วงคะแนน

อย่าเปลี่ยนคะแนน `80` และ `90` ภายใน `activity_entries` เพราะเป็นกติกา `0–100` ที่ต้องรักษาไว้

- [ ] **Step 6: Run the full SupportScore service test**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: PASS ทุก test และไม่มี test ใด fail ด้วย validation คนละสาเหตุจากชื่อ test

- [ ] **Step 7: Commit Server validation**

```powershell
git add app/Support/SupportScoreRules.php app/Services/SupportScoreService.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: enforce support criterion score limits"
```

---

### Task 4: Regression and Build Verification

**Files:**

- Verify only; หากพบ regression ให้ย้อนกลับไปแก้ใน task และไฟล์เจ้าของพฤติกรรมนั้น

**Interfaces:**

- Consumes: Browser validator, Blade input contract และ Server validation จาก Tasks 1–3
- Produces: fresh evidence ว่าคะแนนรายกิจกรรมและ workflow คะแนนสายสนับสนุนเดิมไม่เปลี่ยน

- [ ] **Step 1: Run all JavaScript tests**

Run:

```powershell
npm run test:js
```

Expected: PASS ทุก test

- [ ] **Step 2: Run focused PHP regression suites**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/EvaluateeConfirmationModalTest.php tests/Unit/Support/SupportWeightedScoreTest.php
```

Expected: PASS ทุก test

- [ ] **Step 3: Run the full PHP suite**

Run:

```powershell
php artisan test
```

Expected: PASS ไม่มี failures หรือ errors

- [ ] **Step 4: Build production assets**

Run:

```powershell
npm run build
```

Expected: exit code `0`

- [ ] **Step 5: Check formatting and final diff**

Run:

```powershell
git diff --check
git status --short
git diff --stat
```

Expected: ไม่มี whitespace errors; ไฟล์ที่เปลี่ยนตรงกับ Tasks 1–3 และไฟล์ dirty เดิมที่ไม่เกี่ยวข้องยังไม่ถูกแก้หรือ stage โดยงานนี้

- [ ] **Step 6: Confirm commit and working-tree ownership**

Run:

```powershell
git log -4 --oneline
git status --short
```

Expected: Tasks 1–3 มี commit แยกกัน และ working tree เหลือเฉพาะไฟล์เดิมที่ไม่เกี่ยวกับงานคะแนน ห้าม stage ไฟล์เอกสาร, presentation, temporary files หรือการแก้ไขเดิมที่ไม่เกี่ยวข้อง
