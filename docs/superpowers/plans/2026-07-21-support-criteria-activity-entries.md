# Support Criteria Activity Entries Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ให้ Admin เปิดสิทธิ์เพิ่มกิจกรรมเป็นรายเกณฑ์สายสนับสนุนได้ ผู้ถูกประเมินเพิ่ม/แก้/ลบ Rich Text ได้หลายรายการแบบไม่บังคับ และผู้ประเมินแก้รายการเดิมได้เมื่อระบุเหตุผลพร้อมเก็บประวัติ โดยไม่เปลี่ยนคะแนน หลักฐาน หรือ Flow เดิม

**Architecture:** เพิ่ม `allow_activity_entries` ที่ template และเก็บข้อมูลระดับรายงานใน `support_activity_entries` พร้อม audit ใน `support_activity_entry_histories` ใช้ `SupportActivityEntryService` เป็นกฎกลาง และเรียกจาก `SupportScoreService` ภายใน transaction เดิมของการบันทึกคะแนน UI ใช้รายการใน DOM เป็น draft จนกดปุ่มบันทึกหลักของฟอร์ม และใช้ Summernote/`SafeHtml` เหมือน Rich Text เดิม

**Tech Stack:** Laravel, Eloquent, Blade, vanilla JavaScript, Summernote, Tailwind CSS, Pest/PHPUnit, SQLite test database, Vite

## Global Constraints

- ทำงานบน branch `feat/support` ใน worktree ปัจจุบันตามที่ผู้ใช้กำหนด ห้ามสร้าง worktree ใหม่
- ก่อนเริ่มแต่ละ Task ให้ตรวจ `git status --short` เพราะมี process อื่นทำงานบน branch เดียวกัน
- รักษางานค้างของผู้ใช้ทั้งหมด โดยเฉพาะ `app/Http/Controllers/ReportStructureController.php`; stage เฉพาะ hunk ของฟีเจอร์นี้ด้วย `git add -p` หากไฟล์ยัง dirty
- ห้ามเปลี่ยนสูตรคะแนนถ่วงน้ำหนัก, การบังคับหลักฐาน, comment ราย Role, status transition หรือ validation ของคะแนนเดิม
- รายการกิจกรรมเป็น optional เสมอ แต่ถ้าส่งรายการหนึ่งมา `content` ต้องมีข้อความที่มองเห็นได้จริง
- ใช้ `SafeHtml::richText()` ทุกจุดที่ render Rich Text และใช้ `HasRichText`/`SafeHtml::plainText()` ตรวจข้อความว่าง
- ผู้ถูกประเมินไม่มี activity history; ผู้ประเมินทั้ง 3 Role ต้องมีเหตุผลเฉพาะเมื่อเนื้อหาเปลี่ยน และห้ามสร้าง/ลบ/จัดลำดับรายการ
- Checkbox ถูกปิดต้องซ่อนรายการแต่ไม่ลบข้อมูลเดิม
- ใช้ `vendor\bin\pest.bat` ไม่ใช้ `php artisan test`
- หาก SQLite กลางถูก lock ให้สร้างไฟล์เฉพาะงานใต้ `database/.tmp-support-activity/database/testing.sqlite`, ตั้ง `DB_DATABASE` เป็น path นั้น และลบเฉพาะไฟล์/โฟลเดอร์ว่างที่สร้างด้วย .NET หลังทดสอบ ห้ามลบแบบ recursive

---

### Task 1: Add activity-entry schema and Eloquent models

**Files:**

- Create: `database/migrations/2026_07_21_000001_create_support_activity_entries.php`
- Create: `app/Models/SupportActivityEntry.php`
- Create: `app/Models/SupportActivityEntryHistory.php`
- Modify: `app/Models/SupportCriteria.php`
- Modify: `app/Models/Reports.php`
- Modify: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

- [ ] **Step 1: Write failing schema and relation tests**

เพิ่ม assertions ว่า:

```php
$this->assertTrue(Schema::hasColumn('support_criterias', 'allow_activity_entries'));
$this->assertTrue(Schema::hasTable('support_activity_entries'));
$this->assertTrue(Schema::hasTable('support_activity_entry_histories'));
$this->assertInstanceOf(
    SupportActivityEntry::class,
    (new SupportCriteria)->activityEntries()->getModel()
);
$this->assertInstanceOf(
    SupportActivityEntryHistory::class,
    (new SupportActivityEntry)->histories()->getModel()
);
$this->assertInstanceOf(
    SupportActivityEntry::class,
    (new Reports)->supportActivityEntries()->getModel()
);
```

ใน `SupportCriteriaTemplateTest` เพิ่มกรณีค่า default เป็น `false` และทดสอบ cascade: ลบ report หรือ support criterion แล้ว entry/history ที่เกี่ยวข้องถูกลบ

- [ ] **Step 2: Run the focused tests and confirm RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: fail เพราะ column, tables, models และ relations ยังไม่มี

- [ ] **Step 3: Create the migration**

โครงสร้างสำคัญ:

```php
Schema::table('support_criterias', function (Blueprint $table): void {
    $table->boolean('allow_activity_entries')->default(false)->after('require_evidence');
});

Schema::create('support_activity_entries', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
    $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
    $table->unsignedInteger('sequence');
    $table->text('content');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->index(['report_id', 'support_criteria_id', 'sequence'], 'support_activity_report_criterion_sequence_index');
});

Schema::create('support_activity_entry_histories', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('support_activity_entry_id')
        ->constrained('support_activity_entries')
        ->cascadeOnDelete();
    $table->text('previous_content');
    $table->text('new_content');
    $table->text('reason');
    $table->foreignId('modified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->string('modified_by_role')->nullable();
    $table->timestamps();
});
```

ลำดับ `down()` ต้อง drop history, entry แล้วจึง drop column

- [ ] **Step 4: Add models and relations**

`SupportActivityEntry`:

```php
protected $fillable = [
    'report_id', 'support_criteria_id', 'sequence', 'content', 'created_by', 'updated_by',
];

public function report(): BelongsTo
{
    return $this->belongsTo(Reports::class, 'report_id');
}

public function supportCriteria(): BelongsTo
{
    return $this->belongsTo(SupportCriteria::class);
}

public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater(): BelongsTo
{
    return $this->belongsTo(User::class, 'updated_by');
}

public function histories(): HasMany
{
    return $this->hasMany(SupportActivityEntryHistory::class)->latest();
}
```

`SupportActivityEntryHistory` มี fillable ตาม schema และ relations `entry()` กับ `modifierUser()` ส่วน `SupportCriteria` เพิ่ม `allow_activity_entries` ใน `$fillable`, cast เป็น boolean และ `activityEntries(): HasMany`; `Reports` เพิ่ม `supportActivityEntries(): HasMany`

- [ ] **Step 5: Run focused tests and confirm GREEN**

Run คำสั่งจาก Step 2

Expected: pass

- [ ] **Step 6: Commit**

```powershell
git add database/migrations/2026_07_21_000001_create_support_activity_entries.php app/Models/SupportActivityEntry.php app/Models/SupportActivityEntryHistory.php app/Models/SupportCriteria.php app/Models/Reports.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: add support activity entry schema"
```

---

### Task 2: Add the Admin per-criterion checkbox to create/edit/clone flows

**Files:**

- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`
- Modify: `tests/Feature/SupportCriteriaRichTextEditorTest.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

- [ ] **Step 1: Write failing Admin contract tests**

เพิ่ม tests ให้ตรวจว่า create/edit markup และ scripts มี class/payload เดียวกัน:

```php
expect(view('criteria_config.partials.support-criteria-template')->render())
    ->toContain('class="support_allow_activity_entries')
    ->toContain('อนุญาตให้ผู้ถูกประเมินเพิ่มกิจกรรม/โครงการ');
```

เพิ่ม feature tests สองกรณี:

1. `POST report-structure.store` พร้อม `allow_activity_entries => true` แล้วฐานข้อมูล/API show เป็น `true`
2. `PUT report-structure.update` เปลี่ยนเป็น `false` แล้วฐานข้อมูล/API show เป็น `false`

- [ ] **Step 2: Run focused tests and confirm RED**

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: fail เพราะยังไม่มี checkbox/payload/persistence

- [ ] **Step 3: Add checkbox markup and front-end payload**

วาง checkbox ต่อจาก “บังคับแนบหลักฐาน” ในแต่ละ `.support_criteria_block`:

```html
<label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
    <input type="checkbox"
        class="support_allow_activity_entries h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
    <span>อนุญาตให้ผู้ถูกประเมินเพิ่มกิจกรรม/โครงการ</span>
</label>
```

เพิ่ม key ใน create และ edit collectors:

```js
allow_activity_entries: supportBlock.querySelector('.support_allow_activity_entries')?.checked || false,
```

และใน `populateSupportCriteria()`:

```js
block.querySelector('.support_allow_activity_entries').checked = Boolean(item.allow_activity_entries);
```

การ clone/add block ใช้ logic reset checkbox เดิม (`input.type === 'checkbox'`) เพื่อให้ default ไม่ติ๊ก

- [ ] **Step 4: Persist checkbox in the Admin controller**

เพิ่ม validation ทั้ง store และ update:

```php
'categories.*.evaluation_lists.*.support_criterias.*.allow_activity_entries' => 'sometimes|boolean',
```

เพิ่ม attribute ทั้ง create และ update mapping:

```php
'allow_activity_entries' => (bool) ($supportData['allow_activity_entries'] ?? false),
```

อย่า refactor ส่วนอื่นใน `ReportStructureController.php`; หากไฟล์ยังมีงานค้าง ให้ stage เฉพาะบรรทัด validation/mapping ของ key นี้

- [ ] **Step 5: Run focused tests and confirm GREEN**

Run คำสั่งจาก Step 2

Expected: pass และ Rich Text lifecycle เดิมยังผ่าน

- [ ] **Step 6: Commit only feature hunks**

```powershell
git add resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git add -p app/Http/Controllers/ReportStructureController.php
git diff --cached --check
git commit -m "feat: configure support activity entries"
```

---

### Task 3: Implement role-aware persistence in the existing score transaction

**Files:**

- Create: `app/Services/SupportActivityEntryService.php`
- Modify: `app/Services/SupportScoreService.php`
- Modify: `app/Support/SupportScoreRules.php`
- Create: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Modify: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

- [ ] **Step 1: Write failing evaluatee service tests**

สร้าง fixture ที่ report ผูก criteria version เดียวกับ criterion และ `allow_activity_entries = true` แล้วทดสอบผ่าน `SupportScoreService::persist()`:

- array ว่างบันทึกได้
- รายการใหม่ 2 รายการได้ sequence 1, 2 และ `created_by/updated_by` เป็นผู้ถูกประเมิน
- รอบถัดไปแก้รายการแรก ลบรายการที่สอง และเพิ่มรายการใหม่แล้วข้อมูลเป็น replace-list ตาม payload
- การแก้ของผู้ถูกประเมินไม่สร้าง history
- Rich Text ที่มีแต่ `<p><br></p>` ถูกปฏิเสธ
- criterion ที่ปิด checkbox ถูกปฏิเสธเมื่อส่งรายการใหม่ แต่ข้อมูลเดิมที่ซ่อนอยู่ไม่ถูกลบ
- entry id จาก report/criterion อื่นถูกปฏิเสธ

Payload ตัวอย่าง:

```php
'activity_entries' => [
    ['content' => '<p>โครงการที่หนึ่ง</p>'],
    ['content' => '<p>โครงการที่สอง</p>'],
],
```

- [ ] **Step 2: Write failing reviewer service tests**

ทดสอบ `requireReasonForChanges = true` ว่า:

- content ไม่เปลี่ยน ไม่บังคับเหตุผลและไม่สร้าง history
- content เปลี่ยนแต่เหตุผลว่างถูกปฏิเสธ
- content เปลี่ยนพร้อมเหตุผล อัปเดต `updated_by` และสร้าง history old/new/reason/actor/role
- รายการไม่มี `id`, omission ของ entry เดิม, id ของ criterion/report อื่น และการเปลี่ยนลำดับ ถูกปฏิเสธ

- [ ] **Step 3: Run the service tests and confirm RED**

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: fail เพราะ service และ nested rules ยังไม่มี

- [ ] **Step 4: Extend nested validation rules**

import `HasRichText` แล้วเพิ่มใน `SupportScoreRules::validation()`:

```php
use App\Rules\HasRichText;

'support_list.*.activity_entries' => ['nullable', 'array'],
'support_list.*.activity_entries.*.id' => ['nullable', 'integer'],
'support_list.*.activity_entries.*.content' => ['required', 'string', new HasRichText],
'support_list.*.activity_entries.*.modification_reason' => ['nullable', 'string', 'max:2000'],
```

ไม่ใช้ `exists` เพียงอย่างเดียวเป็น authorization; service ต้องยืนยัน report และ criterion ของ entry อีกชั้น

- [ ] **Step 5: Implement `SupportActivityEntryService`**

ใช้ public method เดียวเพื่อให้ mode ตรงกับกฎคะแนนเดิม:

```php
public function persist(
    Reports $report,
    Collection $allowedCriteria,
    array $items,
    ?User $actor,
    ?string $modifierRole,
    bool $requireReasonForChanges
): void
```

กฎภายใน:

1. map `support_list` ด้วย `support_criteria_id`
2. ข้าม criterion ที่ไม่มี payload; ถ้า criterion ปิด checkbox ให้รับได้เฉพาะ `activity_entries` ว่าง และห้ามลบข้อมูลเดิม
3. evaluatee mode (`false`): ตรวจ id ทุกตัวว่าอยู่ report+criterion, update/create ตามลำดับ payload, delete เฉพาะ id เดิมของ criterion ที่เปิดแต่ไม่ถูกส่ง, แล้วเรียง sequence ใหม่
4. reviewer mode (`true`): payload ids ต้องเท่ากับ existing ids ตามลำดับเดิม ห้าม missing/new/reorder; update เฉพาะ content ที่เปลี่ยน
5. เปรียบเทียบ Rich Text แบบค่าที่เก็บจริง; ถ้า reviewer เปลี่ยน ต้อง `filled(trim(reason))`, บันทึก history ก่อน update
6. method นี้ไม่เปิด transaction เอง เพื่อให้ caller คุม transaction เดียวกับ score/evidence

ตัวอย่าง history:

```php
SupportActivityEntryHistory::create([
    'support_activity_entry_id' => $entry->id,
    'previous_content' => $entry->content,
    'new_content' => $content,
    'reason' => trim($reason),
    'modified_by' => $actor?->id,
    'modified_by_role' => $modifierRole,
]);
```

ใช้ `ValidationException::withMessages()` พร้อม path ระดับรายการ เช่น `support_list.0.activity_entries.1.modification_reason`

- [ ] **Step 6: Integrate with `SupportScoreService` transaction**

เพิ่ม constructor injection:

```php
public function __construct(
    private readonly SupportActivityEntryService $activityEntryService
) {}
```

คง `persist()` signature เดิมเพื่อไม่ต้องเปลี่ยน controller ทั้ง 4 ตัว แล้วเรียก service ภายใน closure `DB::transaction()` หลัง normalize/ownership validation และก่อน return:

```php
$this->activityEntryService->persist(
    $report,
    $allowedCriteria,
    $normalizedItems,
    $actor,
    $modifierRole,
    $requireReasonForChanges
);
```

เพิ่ม test rollback: ทำให้ activity validation ล้มหลัง payload คะแนนถูกเตรียม แล้ว assert ว่าคะแนน หลักฐาน และ activity ไม่มีการเปลี่ยนแปลง

- [ ] **Step 7: Run focused tests and confirm GREEN**

Run คำสั่งจาก Step 3

Expected: pass และ tests คะแนนเดิมไม่เปลี่ยนผล

- [ ] **Step 8: Commit**

```powershell
git add app/Services/SupportActivityEntryService.php app/Services/SupportScoreService.php app/Support/SupportScoreRules.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: persist support activity entries"
```

---

### Task 4: Expose entries and histories through the shared read model

**Files:**

- Modify: `app/Support/SupportCriteriaReadModel.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`

- [ ] **Step 1: Write a failing read-model test**

สร้าง 2 entries และ reviewer history แล้วขยาย expected item ด้วย:

```php
'allow_activity_entries' => true,
'activity_entries' => [[
    'id' => $entry->id,
    'sequence' => 1,
    'content' => '<p>จัดทำรายงานประจำเดือน</p>',
    'histories' => [[
        'previous_content' => '<p>ข้อความเดิม</p>',
        'new_content' => '<p>จัดทำรายงานประจำเดือน</p>',
        'reason' => 'ปรับให้ตรงผลงานจริง',
        'modified_by_name' => $reviewer->display_name,
        'modified_by_role' => 'ผู้ประเมิน',
        'created_at' => $history->created_at->format('d/m/Y H:i'),
    ]],
]],
```

เพิ่มกรณี checkbox false: read model ยังโหลดข้อมูลจากฐานได้เพื่อไม่ทำลายข้อมูล แต่ส่ง `activity_entries => []` ให้ UI เพื่อซ่อนตามสเปก

- [ ] **Step 2: Run and confirm RED**

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

- [ ] **Step 3: Load entries without N+1 and shape the response**

query entries ครั้งเดียวตาม report+criterion ids พร้อม eager-load histories และ modifier:

```php
$activityEntries = SupportActivityEntry::query()
    ->with(['histories.modifierUser:id,prefix,name'])
    ->where('report_id', $report->id)
    ->whereIn('support_criteria_id', $criterionIds)
    ->orderBy('sequence')
    ->get()
    ->groupBy('support_criteria_id');
```

เพิ่ม `allow_activity_entries` และ map `activity_entries` ในแต่ละ criterion; เมื่อ flag เป็น false ให้คืน array ว่างโดยไม่ delete rows

- [ ] **Step 4: Run and confirm GREEN**

Run คำสั่งจาก Step 2

- [ ] **Step 5: Commit**

```powershell
git add app/Support/SupportCriteriaReadModel.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
git commit -m "feat: expose support activity entry history"
```

---

### Task 5: Add evaluatee/reviewer Rich Text activity editing to the shared table

**Files:**

- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `resources/views/components/unified-evaluation.blade.php`
- Modify: `resources/views/components/unified-evaluator.blade.php`
- Modify: `resources/views/components/unified-director.blade.php`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `tests/Feature/SupportCriteriaRichTextEditorTest.php`
- Create: `tests/js/support-activity-entries.test.mjs`

- [ ] **Step 1: Write failing Blade contract tests**

เพิ่ม prop ที่ชัดเจน:

```blade
@props([
    'items' => [],
    'readonly' => false,
    'evidenceEditable' => false,
    'requireReason' => false,
    'activityEntryRole' => 'readonly',
])
```

ทดสอบว่า:

- flag false ไม่มีปุ่มเพิ่มและแสดงเฉพาะ Admin `activity_name`
- flag true แสดง entries ทั้ง desktop table และ mobile card ผ่าน `SafeHtml`
- evaluatee editable มีปุ่มเพิ่ม/แก้/ลบ
- reviewer editable มีแก้และเหตุผล แต่ไม่มีเพิ่ม/ลบ
- readonly ไม่มี control แต่ยังเห็นรายการ
- script contract มี nested input names เช่น `support_list[17][activity_entries][0][content]`
- Rich Text อันตรายใน entry และ history ถูก `SafeHtml` ตัดออกก่อนแสดงผล

- [ ] **Step 2: Write failing JavaScript behavior tests**

แยก pure helpers ที่ทดสอบด้วย Node ได้ใน script หรือ module เล็กตาม pattern ของ `tests/js`:

- reindex nested field names หลังเพิ่ม/ลบ
- visually empty HTML ถูกปฏิเสธก่อนปิด modal
- evaluatee snapshot/cancel คืน add/edit/delete ที่ยังไม่บันทึกใน modal
- reviewer payload คงทุก id/sequence และส่ง reason เฉพาะรายการที่แก้

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php
npm run test:js -- --test-name-pattern="support activity"
```

Expected: fail เพราะ UI/behavior ยังไม่มี

- [ ] **Step 3: Render Admin heading plus activity entries in both responsive modes**

ใน desktop activity cell และ mobile heading:

```blade
<div class="support-criteria-rich-text">
    {!! \App\Support\SafeHtml::richText($item['activity_name'] ?? '') !!}
</div>
@if (!empty($item['allow_activity_entries']))
    <ol data-support-activity-list="{{ $item['id'] }}">
        @foreach ($item['activity_entries'] ?? [] as $entry)
            <li>{!! \App\Support\SafeHtml::richText($entry['content']) !!}</li>
        @endforeach
    </ol>
@endif
```

ใช้ partial/Blade fragment เดียวกันถ้าทำได้เพื่อไม่ให้ table/card แสดงไม่ตรงกัน และรักษา `table-fixed`/ไม่มี horizontal scroll เดิม

- [ ] **Step 4: Add nested editor state to each hidden support item**

สำหรับ criterion ที่เปิด flag ให้ render entry rows ใน `[data-support-editor-store]` พร้อม hidden id/content/reason inputs และ data attributes สำหรับ original content/sequence เพิ่ม modal ย่อยหรือ section ใน modal จัดการเดิมที่มี:

- `+ เพิ่มกิจกรรม/โครงการ` เฉพาะ evaluatee
- `แก้ไข` สำหรับ evaluatee/reviewer เมื่อไม่ readonly
- `ลบ` เฉพาะ evaluatee
- Summernote textarea ใช้ toolbar/options เดียวกับ Admin
- reviewer แสดงช่อง “เหตุผลที่แก้ไขกิจกรรม/โครงการ” ต่อ entry ที่แก้
- history details แสดง old/new ด้วย `SafeHtml::richText()` และ actor/role/time เป็น escaped text

ปุ่ม “บันทึก” ใน modal อัปเดต DOM/hidden inputs เท่านั้น ห้ามยิง request; request เกิดจากปุ่ม “บันทึกฉบับร่าง/ยืนยันและส่ง” ของฟอร์มเดิม

- [ ] **Step 5: Extend shared modal JavaScript safely**

เพิ่ม functions ที่มีหน้าที่ชัดเจน:

```js
const snapshotActivityEntries = (item) => { /* ids, HTML, reasons */ };
const restoreActivityEntries = (item, snapshot) => { /* restore DOM */ };
const reindexActivityEntries = (item) => { /* rebuild nested names and sequence */ };
const initializeActivityEditor = (textarea) => { /* Summernote options */ };
const destroyActivityEditor = (textarea) => { /* prevent duplicate editor wrappers */ };
const activityHtmlHasVisibleText = (html) => { /* decode/strip and trim */ };
const updateActivityDisplays = (item) => { /* desktop + mobile */ };
```

ผูก lifecycle กับ open/save/cancel/Escape ของ modal เดิม ระวัง focus trap และ restore focus เดิม เพิ่ม validation เข้า `window.validateSupportCriteria()` เพื่อให้ main submit ไม่ส่ง Rich Text ว่าง

- [ ] **Step 6: Set role props at all component call sites**

```blade
{{-- unified-evaluation: ผู้ถูกประเมิน --}}
activity-entry-role="evaluatee"

{{-- unified-evaluator และ unified-director: ผู้ประเมินทุก Role --}}
activity-entry-role="reviewer"
```

ให้ `$readonly` ชนะ role เสมอ: หาก readonly ห้ามแสดง editor/control ใด ๆ

- [ ] **Step 7: Run focused tests and confirm GREEN**

Run คำสั่งจาก Step 2 และ:

```powershell
php artisan view:clear
php artisan view:cache
npm run build
```

Expected: Blade contracts, JS behavior, Blade compilation และ Vite build ผ่าน

- [ ] **Step 8: Commit**

```powershell
git add resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php resources/views/components/unified-evaluation.blade.php resources/views/components/unified-evaluator.blade.php resources/views/components/unified-director.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/js/support-activity-entries.test.mjs
git commit -m "feat: edit support activity entries"
```

---

### Task 6: Cover every Role and preserve the existing evaluation Flow

**Files:**

- Modify: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`
- Modify: `tests/Feature/Evaluation/EvaluateeTest.php`
- Modify: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

- [ ] **Step 1: Add failing evaluatee route-level tests**

ผ่าน route `evaluation_score.store` ทดสอบทั้ง Draft และ Pending:

- ไม่มี entry ก็ save/submit ได้
- add/edit/delete entries ถูก persist พร้อม score/evidence เดิม
- activity validation fail แล้ว report status, score และ evidence ไม่เปลี่ยน
- actor/report/status ที่ Flow เดิมไม่อนุญาตยังถูกปฏิเสธ

- [ ] **Step 2: Add failing reviewer dataset tests for all three Roles**

ขยาย dataset เดิม `ผู้ประเมิน`, `กรรมการ`, `ผู้บริหาร` ให้ส่ง activity payload และยืนยันว่า:

- แต่ละ Role แก้ content พร้อมเหตุผลได้และ comment ของ Role ยังถูกเก็บ
- ไม่มีเหตุผลแล้ว validation path ชี้ activity entry
- พยายาม add/delete/reorder ถูกปฏิเสธและ status/score/activity เดิม rollback
- draft และ approve status ใช้กติกาเดียวกัน

- [ ] **Step 3: Run route-level tests and confirm RED, then make only necessary integration fixes**

```powershell
vendor\bin\pest.bat tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected initial RED: เปิดเผยจุดที่ payload/transaction/validation ยังไม่ครบ จากนั้นแก้เฉพาะ service/rules/shared component ที่ Task ก่อนหน้าสร้าง ห้ามเปลี่ยน status mapping ของ controller

- [ ] **Step 4: Run focused support suite**

```powershell
vendor\bin\pest.bat tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/EvaluateeTest.php
npm run test:js -- --test-name-pattern="support"
```

- [ ] **Step 5: Commit flow tests and fixes**

```powershell
git add tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git add -p app/Services/SupportActivityEntryService.php app/Services/SupportScoreService.php app/Support/SupportScoreRules.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php
git diff --cached --check
git commit -m "test: cover support activity entry flow"
```

หากไม่มี integration fix และ test files ถูก commit ใน Task ก่อนแล้ว ไม่ต้องสร้าง empty commit

---

### Task 7: Full verification and manual acceptance check

**Files:**

- No planned source changes

- [ ] **Step 1: Re-check workspace and migration state**

```powershell
git status --short
php artisan migrate:status
```

ตรวจว่า migration ใหม่ยังไม่ชนชื่อกับงานที่เข้ามาระหว่างพัฒนา

- [ ] **Step 2: Run full PHP suite using an isolated SQLite file**

```powershell
$taskDbDir = Join-Path (Get-Location) 'database/.tmp-support-activity/database'
[System.IO.Directory]::CreateDirectory($taskDbDir) | Out-Null
$taskDb = Join-Path $taskDbDir 'testing.sqlite'
[System.IO.File]::WriteAllBytes($taskDb, [byte[]]@())
$env:DB_DATABASE = $taskDb
vendor\bin\pest.bat
Remove-Item Env:DB_DATABASE
[System.IO.File]::Delete($taskDb)
[System.IO.Directory]::Delete($taskDbDir)
[System.IO.Directory]::Delete((Split-Path $taskDbDir -Parent))
```

Expected: full suite pass; ถ้าเกิด failure ที่ไม่เกี่ยวข้อง ให้แยกหลักฐานและอย่าแก้กว้างโดยไม่มีสาเหตุ

- [ ] **Step 3: Run final static/build checks**

```powershell
npm run test:js
npm run build
php artisan view:clear
php artisan view:cache
git diff --check
```

Expected: ทุกคำสั่ง exit 0

- [ ] **Step 4: Manual acceptance in browser**

ตรวจอย่างน้อย:

1. Admin ไม่ติ๊ก: เห็นเฉพาะข้อความ Admin ไม่มีปุ่มเพิ่ม
2. Admin ติ๊ก: ผู้ถูกประเมินเพิ่ม 2 รายการ แก้ 1 ลบ 1 แล้วกด “บันทึกฉบับร่าง”; reload แล้วยังถูกต้อง
3. ไม่เพิ่มรายการเลยแล้วยืนยันส่งได้
4. ผู้ประเมินเห็นรายการเดิม ไม่มีเพิ่ม/ลบ; แก้โดยไม่ใส่เหตุผลไม่ได้
5. ใส่เหตุผลแล้วบันทึกได้และประวัติแสดง old/new/actor/role/time
6. คะแนน หลักฐาน จำนวนลิงก์ comment ราย Role และการส่งต่อ Flow เดิมยังทำงาน
7. desktop และ mobile/card แสดงรายการเหมือนกัน และไม่มี horizontal scroll กลับมา

- [ ] **Step 5: Inspect final diff and commits**

```powershell
git status --short
git log --oneline --decorate -8
git diff --stat 8a4a1de..HEAD
```

ยืนยันว่าไม่มีไฟล์งานค้างเดิมหรือเอกสาร unrelated ถูก stage/commit และรายงานผลทดสอบตาม output จริงเท่านั้น
