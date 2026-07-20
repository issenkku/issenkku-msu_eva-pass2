# Support Criteria Table Width Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ขยายหน้าประเมินทุก Role และกำหนดความกว้างขั้นต่ำของตารางเกณฑ์สายสนับสนุนให้ข้อความอ่านง่ายบนจอกว้าง โดยคง responsive behavior เดิม

**Architecture:** ใช้ class กลาง `evaluation-form-shell` ร่วมกับ `w-full max-w-7xl` ใน wrapper ของทุกหน้าประเมิน เพื่อให้ CSS responsive อ้าง semantic class แทน utility class ทั่วไป ตารางเกณฑ์สายสนับสนุนใช้ `min-w-[1180px]` และความกว้างขั้นต่ำรายคอลัมน์ภายใน `overflow-x-auto` เดิม

**Tech Stack:** Laravel 11, Blade components, Tailwind CSS, Pest/PHPUnit, Vite

## Global Constraints

- ทำงานบน branch `feat/support` โดยตรงและไม่สร้าง worktree
- wrapper หน้าประเมินเปลี่ยนจาก `max-w-4xl` เป็น `evaluation-form-shell mx-auto w-full max-w-7xl space-y-6`
- ตารางเดสก์ท็อปใช้ `min-w-[1180px] w-full` และไม่ใช้ `table-fixed`
- กิจกรรมมี `min-w-[240px]` และตัวชี้วัดมี `min-w-[360px]`
- จอขนาดกลางเลื่อนแนวนอนเฉพาะ container ตาราง
- ต่ำกว่า `md` ยังคงใช้การ์ดเดิม
- Modal คง `max-w-3xl`
- ไม่เปลี่ยนข้อมูล, payload, validation, สิทธิ์ Role หรือ Flow การบันทึก
- ไม่แก้ไฟล์ dirty ที่ไม่เกี่ยวข้องกับฟีเจอร์

---

## File Structure

- `resources/views/evaluatee/evaluation.blade.php` — wrapper หน้าผู้ถูกประเมิน
- `resources/views/partials/evaluation-evaluator-form.blade.php` — wrapper ร่วมของผู้ประเมิน
- `resources/views/partials/evaluation-approval-form.blade.php` — wrapper ร่วมของกรรมการและผู้บริหาร
- `resources/views/dashboard/admin.blade.php` — wrapper หน้าผู้ดูแลระบบที่ใช้ฟอร์มเดียวกัน
- `resources/views/partials/evaluation-form-styles.blade.php` — responsive padding ของ semantic wrapper
- `resources/views/components/support-criteria-table.blade.php` — min-width ของตารางและคอลัมน์
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — source contracts สำหรับ wrapper และตาราง

### Task 1: Widen Evaluation Shells and Support Table

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/evaluatee/evaluation.blade.php`
- Modify: `resources/views/partials/evaluation-evaluator-form.blade.php`
- Modify: `resources/views/partials/evaluation-approval-form.blade.php`
- Modify: `resources/views/dashboard/admin.blade.php`
- Modify: `resources/views/partials/evaluation-form-styles.blade.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`

**Interfaces:**
- Produces: `.evaluation-form-shell` on every evaluation-page wrapper
- Produces: `max-w-7xl` layout contract and removes `max-w-4xl` from scoped evaluation wrappers
- Produces: `min-w-[1180px]` support table with exact per-column minimum widths
- Preserves: `hidden md:table`, `md:hidden`, and table-container `overflow-x-auto`

- [ ] **Step 1: Write failing wrapper and table-width tests**

Append these tests to `tests/Feature/SupportCriteriaEvaluationViewTest.php`:

```php
test('all evaluation form shells use the wider shared layout', function () {
    foreach ([
        'evaluatee/evaluation.blade.php',
        'partials/evaluation-evaluator-form.blade.php',
        'partials/evaluation-approval-form.blade.php',
        'dashboard/admin.blade.php',
    ] as $viewPath) {
        $source = file_get_contents(resource_path("views/{$viewPath}"));

        expect($source)
            ->toContain('evaluation-form-shell')
            ->toContain('w-full max-w-7xl')
            ->not->toContain('max-w-4xl');
    }

    $styles = file_get_contents(resource_path('views/partials/evaluation-form-styles.blade.php'));
    expect($styles)
        ->toContain('.evaluation-form-shell')
        ->not->toContain('.max-w-4xl');
});

test('support criteria desktop table reserves readable column widths', function () {
    $source = file_get_contents(resource_path('views/components/support-criteria-table.blade.php'));

    expect($source)
        ->toContain('min-w-[1180px]')
        ->toContain('min-w-[240px]')
        ->toContain('min-w-[360px]')
        ->toContain('min-w-[110px]')
        ->toContain('min-w-[80px]')
        ->toContain('min-w-[100px]')
        ->toContain('min-w-[120px]')
        ->toContain('min-w-[90px]')
        ->not->toContain('table-fixed');
});
```

- [ ] **Step 2: Run focused tests and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
```

Expected: FAIL because wrappers still use `max-w-4xl` and the table still uses `table-fixed` without the required minimum widths.

- [ ] **Step 3: Apply the shared wide wrapper to every evaluation page**

Replace the opening wrapper in all four scoped views with the exact class list:

```blade
<div class="evaluation-form-shell mx-auto w-full max-w-7xl space-y-6">
```

The four exact replacements are:

```text
resources/views/evaluatee/evaluation.blade.php:
<div class="max-w-4xl mx-auto space-y-6">

resources/views/partials/evaluation-evaluator-form.blade.php:
<div class="mx-auto max-w-4xl space-y-6">

resources/views/partials/evaluation-approval-form.blade.php:
<div class="mx-auto max-w-4xl space-y-6">

resources/views/dashboard/admin.blade.php:
<div class="max-w-4xl mx-auto space-y-6">
```

- [ ] **Step 4: Scope mobile padding to the semantic wrapper**

In `resources/views/partials/evaluation-form-styles.blade.php`, replace:

```css
.max-w-4xl {
    max-width: 100%;
    padding: 0 1rem;
}
```

with:

```css
.evaluation-form-shell {
    max-width: 100%;
    padding: 0 1rem;
}
```

- [ ] **Step 5: Give the support table and columns readable minimum widths**

Change the desktop table opening tag to:

```blade
<table class="hidden min-w-[1180px] w-full border-collapse text-left text-sm md:table">
```

Use these exact header classes:

```blade
<th scope="col" class="w-16 px-3 py-3 text-center">ลำดับ</th>
<th scope="col" class="min-w-[240px] px-3 py-3">กิจกรรม/โครงการ/งาน</th>
<th scope="col" class="min-w-[360px] px-3 py-3">ตัวชี้วัด/เกณฑ์การประเมิน</th>
<th scope="col" class="min-w-[110px] px-3 py-3 text-right">ระดับค่าเป้าหมาย</th>
<th scope="col" class="min-w-[80px] px-3 py-3 text-right">น้ำหนัก</th>
<th scope="col" class="min-w-[100px] px-3 py-3 text-right">ค่าคะแนนที่ได้</th>
<th scope="col" class="min-w-[120px] px-3 py-3 text-right">คะแนนถ่วงน้ำหนัก</th>
<th scope="col" class="min-w-[90px] px-3 py-3 text-center">หลักฐาน</th>
@if (!$readonly)
    <th scope="col" class="min-w-[100px] px-3 py-3 text-center">จัดการ</th>
@endif
```

Keep the existing outer `<div class="hidden overflow-x-auto md:block">` unchanged so only the table region scrolls.

- [ ] **Step 6: Run focused render and Blade checks**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
php artisan view:cache
```

Expected: all focused tests PASS and Blade templates cache successfully.

- [ ] **Step 7: Run full verification**

Run:

```powershell
vendor\bin\pest --compact
vendor\bin\pint --test tests\Feature\SupportCriteriaEvaluationViewTest.php
npm run build
git diff --check
```

Expected: Pest has 0 failures, Pint passes, Vite exits 0, and `git diff --check` prints no output.

- [ ] **Step 8: Commit the width adjustment**

```powershell
git add -- tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/evaluatee/evaluation.blade.php resources/views/partials/evaluation-evaluator-form.blade.php resources/views/partials/evaluation-approval-form.blade.php resources/views/dashboard/admin.blade.php resources/views/partials/evaluation-form-styles.blade.php resources/views/components/support-criteria-table.blade.php
git commit -m "style: widen support criteria evaluation table"
```

## Completion Criteria

- ทุกหน้าประเมินในขอบเขตใช้ `evaluation-form-shell` และ `max-w-7xl`
- ตารางเกณฑ์สายสนับสนุนมีความกว้างขั้นต่ำ 1180px พร้อมสัดส่วนคอลัมน์ตามแบบ
- จอกว้างแสดงข้อมูลโดยไม่บีบผิดปกติ
- จอขนาดกลางเลื่อนเฉพาะตารางและมือถือยังใช้การ์ดเดิม
- Modal และ Flow การบันทึกไม่เปลี่ยน
- Focused tests, full PHP suite, Blade cache, Pint, Vite build และ diff check ผ่าน
