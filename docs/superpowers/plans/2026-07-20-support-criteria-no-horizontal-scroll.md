# Support Criteria Table Without Horizontal Scrolling Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ทำให้เกณฑ์สายสนับสนุนไม่มีแถบเลื่อนแนวนอน โดยใช้ตารางที่พอดีกรอบบนจอตั้งแต่ 1024px และการ์ดบนจอที่แคบกว่า

**Architecture:** ปรับเฉพา Blade component ร่วมที่แสดงเกณฑ์สายสนับสนุน ตารางจะใช้ fixed layout และสัดส่วนคอลัมน์ที่รวมกันพอดีกรอบ ส่วน responsive breakpoint จะสลับระหว่างตารางและการ์ดที่ `lg` โดยไม่เปลี่ยน Modal, JavaScript, payload หรือ server flow

**Tech Stack:** Laravel Blade, Tailwind CSS, Pest PHP, Vite

## Global Constraints

- ทำงานโดยตรงบน branch `feat/support` และไม่สร้าง worktree
- ทุกขนาดหน้าจอต้องไม่มี horizontal scrolling ในส่วนเกณฑ์สายสนับสนุน
- viewport กว้างตั้งแต่ 1024px แสดงตาราง; viewport ที่แคบกว่าแสดงการ์ด
- ข้อความยาวต้องตัดขึ้นบรรทัดใหม่ภายในเซลล์และไม่ดันตารางให้ล้นกรอบ
- คง wrapper `max-w-7xl`, Modal, ลำดับข้อมูล, validation, calculation, payload และ Flow การบันทึกเดิม
- ไม่แก้ไขหรือ commit ไฟล์ค้างที่ไม่เกี่ยวข้องกับงานนี้

---

## File Structure

- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php` — กำหน regression contract ของ desktop table, card breakpoint และการไม่มี horizontal overflow
- Modify: `resources/views/components/support-criteria-table.blade.php` — รับผิดชอบ layout ของตาราง/การ์ด และการตัดบรรทัดข้อความ

### Task 1: Make the support criteria layout fit without horizontal scrolling

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php:177-190`
- Modify: `resources/views/components/support-criteria-table.blade.php:21-85`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: Blade props `items`, `readonly`, `evidenceEditable`, and `requireReason`; Tailwind breakpoint `lg` = 1024px
- Produces: the same rendered fields, `data-support-*` attributes, Modal triggers, and form names as before; only responsive presentation changes

- [ ] **Step 1: Replace the width regression test with the no-scroll contract**

Replace the test at the end of `tests/Feature/SupportCriteriaEvaluationViewTest.php` with:

```php
test('support criteria uses a fixed desktop table and cards without horizontal scrolling', function () {
    $source = file_get_contents(resource_path('views/components/support-criteria-table.blade.php'));

    expect($source)
        ->toContain('hidden w-full table-fixed')
        ->toContain('lg:table')
        ->toContain('lg:hidden')
        ->toContain('w-[19%]')
        ->toContain('w-[28%]')
        ->toContain('break-words')
        ->not->toContain('overflow-x-auto')
        ->not->toContain('min-w-[1180px]')
        ->not->toContain('min-w-[240px]')
        ->not->toContain('min-w-[360px]')
        ->not->toContain('md:table')
        ->not->toContain('md:hidden');
});
```

- [ ] **Step 2: Run the focused test and verify that the new contract fails**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: FAIL in `support criteria uses a fixed desktop table and cards without horizontal scrolling` because the component still contains `overflow-x-auto`, `min-w-[1180px]`, and the `md` breakpoint.

- [ ] **Step 3: Replace the horizontally scrollable table wrapper with a fixed table**

Remove the desktop wrapper `<div class="hidden overflow-x-auto md:block">` and its closing tag. Change the table opening tag and headers to:

```blade
<table class="hidden w-full table-fixed border-collapse text-left text-sm lg:table">
    <thead class="bg-amber-50 text-xs font-semibold uppercase tracking-wide text-amber-950">
        <tr>
            <th scope="col" class="w-[5%] break-words px-2 py-3 text-center">ลำดับ</th>
            <th scope="col" class="w-[19%] break-words px-2 py-3">กิจกรรม/โครงการ/งาน</th>
            <th scope="col" class="w-[28%] break-words px-2 py-3">ตัวชี้วัด/เกณฑ์การประเมิน</th>
            <th scope="col" class="w-[9%] break-words px-2 py-3 text-right">ระดับค่าเป้าหมาย</th>
            <th scope="col" class="w-[7%] break-words px-2 py-3 text-right">น้ำหนัก</th>
            <th scope="col" class="w-[8%] break-words px-2 py-3 text-right">ค่าคะแนนที่ได้</th>
            <th scope="col" class="w-[9%] break-words px-2 py-3 text-right">คะแนนถ่วงน้ำหนัก</th>
            <th scope="col" class="w-[7%] break-words px-2 py-3 text-center">หลักฐาน</th>
            @if (!$readonly)
                <th scope="col" class="w-[8%] break-words px-2 py-3 text-center">จัดการ</th>
            @endif
        </tr>
    </thead>
```

The editable table percentages total 100%. In read-only mode the browser redistributes the unused management-column space across the remaining fixed-layout columns.

- [ ] **Step 4: Keep long row content inside its allocated columns**

Change the two long text cells from:

```blade
<td class="px-3 py-4 font-medium text-slate-900">{{ $item['activity_name'] }}</td>
<td class="px-3 py-4 leading-6">{{ $item['indicator'] }}</td>
```

to:

```blade
<td class="break-words px-2 py-4 font-medium text-slate-900">{{ $item['activity_name'] }}</td>
<td class="break-words px-2 py-4 leading-6">{{ $item['indicator'] }}</td>
```

Change the remaining desktop row cell horizontal padding from `px-3` to `px-2` so the nine columns retain usable content space without changing their values or `data-support-*` attributes.

- [ ] **Step 5: Move the card/table switch to the approved breakpoint**

Change the card container from:

```blade
<div class="space-y-3 p-4 md:hidden">
```

to:

```blade
<div class="space-y-3 p-4 lg:hidden">
```

- [ ] **Step 6: Run focused verification**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
vendor/bin/pint --test tests/Feature/SupportCriteriaEvaluationViewTest.php
php artisan view:clear
php artisan view:cache
```

Expected: all focused tests PASS, Pint exits with code 0, and Blade views compile successfully.

- [ ] **Step 7: Run regression and asset verification**

Run:

```powershell
php artisan test
npm run build
git diff --check -- resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: the full PHP suite passes, Vite build succeeds, and `git diff --check` returns no output.

- [ ] **Step 8: Review the scoped diff and commit only the two implementation files**

First inspect whether another task still has an unstaged hunk in the shared test file:

```powershell
git diff -- resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git add -- resources/views/components/support-criteria-table.blade.php
git add -p -- tests/Feature/SupportCriteriaEvaluationViewTest.php
git diff --cached --name-only
git commit -m "style: remove support table horizontal scrolling"
```

At the `git add -p` prompt, stage only the hunk that replaces the old `support criteria desktop table reserves readable column widths` test. Do not stage the concurrent rich-text test hunk near the beginning of the file if it is still uncommitted.

Expected staged file list before commit:

```text
resources/views/components/support-criteria-table.blade.php
tests/Feature/SupportCriteriaEvaluationViewTest.php
```
