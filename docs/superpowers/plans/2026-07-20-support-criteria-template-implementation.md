# Support Criteria Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่ม Checkbox และฟอร์ม Template เกณฑ์สำหรับสายสนับสนุนในหน้า Create/Edit พร้อมบันทึก โหลด แก้ไข ลบ และจัดลำดับข้อมูล โดยยังไม่เพิ่มการกรอกหรือคำนวณคะแนนจริง

**Architecture:** เก็บรายการสายสนับสนุนแบบ flat list ในตาราง `support_criterias` ซึ่งผูกกับ `evaluation_lists` และ cascade delete ตาม parent API เดิมของ `ReportStructureController` รับและส่ง `support_criterias` ควบคู่กับโครงสร้างปริมาณและคุณภาพ ส่วน Blade ใช้ partial ฟอร์มร่วมกันระหว่างหน้า Create/Edit และ JavaScript เดิมเป็นผู้ควบคุม show/hide, clone, sequence และ payload

**Tech Stack:** Laravel 11, PHP 8.2, Eloquent, Blade, vanilla JavaScript, Tailwind CSS, Pest/PHPUnit, SQLite test database

## Global Constraints

- Checkbox “เกณฑ์สำหรับสายสนับสนุน” ต้องอยู่ต่อจาก “เกณฑ์ด้านคุณภาพ” และเลือกพร้อม Checkbox เดิมได้
- ช่อง Template มีเพียง `activity_name`, `indicator`, `target_value`, `weight` และ `sequence`
- `target_value` เป็นตัวเลขตั้งแต่ 0 ขึ้นไปและรองรับทศนิยม
- `weight` เป็นตัวเลขมากกว่า 0 ไม่เกิน 100 และรองรับทศนิยม
- แสดงสูตร `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100` เพื่ออ้างอิงเท่านั้น
- ห้ามเพิ่มช่องคะแนน ห้ามคำนวณคะแนน และห้ามแก้หน้าของผู้ถูกประเมินหรือผู้ประเมินในงานนี้
- ไม่บังคับให้ผลรวมน้ำหนักเท่ากับ 100
- รักษาพฤติกรรมเกณฑ์ปริมาณและคุณภาพเดิมทั้งหมด

---

## File Structure

**Create**

- `database/migrations/2026_07_20_000001_create_support_criterias_table.php` — schema และ cascade relationship
- `app/Models/SupportCriteria.php` — fillable, casts และ relation กลับไปยังรายการประเมิน
- `resources/views/criteria_config/partials/support-criteria-template.blade.php` — markup ร่วมของฟอร์มสายสนับสนุน
- `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php` — add/delete/populate รายการสายสนับสนุนในหน้า Edit
- `tests/Feature/Report/SupportCriteriaTemplateTest.php` — API create/show/update/delete/validation
- `tests/Feature/SupportCriteriaTemplateViewTest.php` — contract ของ markup และ JavaScript hooks

**Modify**

- `app/Models/EvaluationList.php` — `supportCriterias(): HasMany`
- `app/Http/Controllers/ReportStructureController.php` — validation, persistence, serialization และ update sync
- `resources/views/criteria_config/partials/create-evaluation-template.blade.php` — Checkbox และ include ฟอร์มร่วม
- `resources/views/criteria_config/partials/edit-evaluation-template.blade.php` — Checkbox และ include ฟอร์มร่วม
- `resources/views/criteria_config/partials/create-script.blade.php` — show/hide, clone, add/delete/reorder และ collect payload
- `resources/views/criteria_config/partials/edit-script.blade.php` — include support handler
- `resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php` — show/hide container สายสนับสนุน
- `resources/views/criteria_config/partials/script-edit-evaluation-handlers.blade.php` — reset support section เมื่อ clone รายการประเมิน
- `resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php` — ล้าง support identity และเหลือ template row เดียว
- `resources/views/criteria_config/partials/script-edit-sequence-updaters.blade.php` — แสดงลำดับรายการสายสนับสนุน
- `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php` — bind add/delete support actions
- `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php` — detect และ populate support data
- `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php` — validate และสร้าง `support_criterias` payload

---

### Task 1: Support criteria persistence model

**Files:**

- Create: `database/migrations/2026_07_20_000001_create_support_criterias_table.php`
- Create: `app/Models/SupportCriteria.php`
- Modify: `app/Models/EvaluationList.php`
- Test: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**

- Produces: ตาราง `support_criterias`
- Produces: `EvaluationList::supportCriterias(): HasMany`
- Produces: `SupportCriteria::evaluationList(): BelongsTo`
- Produces fields: `support_criteria_id`, `evaluation_list_id`, `sequence`, `activity_name`, `indicator`, `target_value`, `weight`

- [ ] **Step 1: Write the failing schema test**

สร้าง `tests/Feature/Report/SupportCriteriaTemplateTest.php`:

```php
<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\SupportCriteria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupportCriteriaTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_criteria_schema_exists(): void
    {
        $this->assertTrue(Schema::hasColumns('support_criterias', [
            'id',
            'evaluation_list_id',
            'sequence',
            'activity_name',
            'indicator',
            'target_value',
            'weight',
            'created_at',
            'updated_at',
        ]));
    }
}
```

- [ ] **Step 2: Run the schema test and verify RED**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php --filter=schema
```

Expected: FAIL because table `support_criterias` does not exist.

- [ ] **Step 3: Add the migration**

Create `database/migrations/2026_07_20_000001_create_support_criterias_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_list_id')
                ->constrained('evaluation_lists')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('activity_name');
            $table->text('indicator');
            $table->decimal('target_value', 10, 2);
            $table->decimal('weight', 5, 2);
            $table->timestamps();

            $table->index(['evaluation_list_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_criterias');
    }
};
```

- [ ] **Step 4: Run the schema test and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 5: Add a failing relationship and cascade test**

เพิ่มใน `SupportCriteriaTemplateTest`:

```php
public function test_evaluation_list_owns_ordered_support_criteria_and_cascades_deletes(): void
{
    $version = CriteriaVersion::factory()->create();
    $category = Category::factory()->create(['criteria_version_id' => $version->id]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $version->id,
        'categorie_id' => $category->id,
    ]);

    SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 2,
        'activity_name' => 'กิจกรรมที่สอง',
        'indicator' => 'ตัวชี้วัดที่สอง',
        'target_value' => 80,
        'weight' => 40,
    ]);
    SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 1,
        'activity_name' => 'กิจกรรมแรก',
        'indicator' => 'ตัวชี้วัดแรก',
        'target_value' => 90.5,
        'weight' => 60,
    ]);

    $this->assertSame(['กิจกรรมแรก', 'กิจกรรมที่สอง'], $evaluationList->supportCriterias->pluck('activity_name')->all());
    $this->assertSame('90.50', $evaluationList->supportCriterias->first()->target_value);

    $evaluationList->delete();

    $this->assertDatabaseCount('support_criterias', 0);
}
```

- [ ] **Step 6: Run the relationship test and verify RED**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php --filter=owns_ordered
```

Expected: FAIL because `SupportCriteria` and `EvaluationList::supportCriterias()` do not exist.

- [ ] **Step 7: Add model and relationships**

Create `app/Models/SupportCriteria.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_list_id',
        'sequence',
        'activity_name',
        'indicator',
        'target_value',
        'weight',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class);
    }
}
```

เพิ่ม import และ method ใน `app/Models/EvaluationList.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function supportCriterias(): HasMany
{
    return $this->hasMany(SupportCriteria::class)->orderBy('sequence');
}
```

- [ ] **Step 8: Run Task 1 tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 9: Commit Task 1**

```powershell
git add app/Models/EvaluationList.php app/Models/SupportCriteria.php database/migrations/2026_07_20_000001_create_support_criterias_table.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: add support criteria template model"
```

---

### Task 2: Create, validate, and show support criteria through the report structure API

**Files:**

- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**

- Consumes: `EvaluationList::supportCriterias()` from Task 1
- Consumes payload: `categories.*.evaluation_lists.*.support_criterias`
- Produces response: `categories[*].evaluation_lists[*].support_criterias[*]`

- [ ] **Step 1: Add authenticated API test setup and payload helper**

เพิ่ม imports, properties, `setUp()` และ helper ต่อไปนี้ใน test class:

```php
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

private User $admin;

protected function setUp(): void
{
    parent::setUp();

    Role::create(['name' => 'admin']);
    $department = \Database\Factories\DepartmentFactory::new()->create();
    $position = \Database\Factories\PositionFactory::new()->create();
    $this->admin = User::factory()->create([
        'employee_id' => 'SUPPORT-ADMIN',
        'password' => Hash::make('password'),
        'status' => 'active',
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin, 'web');
}

private function payload(array $supportCriterias): array
{
    return [
        'version_name' => 'Support Template '.uniqid(),
        'created_by' => $this->admin->id,
        'report_datas' => [[
            'report_title' => 'แบบประเมินสายสนับสนุน',
            'report_description' => null,
            'assessment_type' => 'support',
            'comment' => null,
        ]],
        'categories' => [[
            'main_categories' => 'ผลสัมฤทธิ์ของงาน',
            'sub_categories' => 'งานตามภารกิจ',
            'sequence' => 1,
            'evaluation_lists' => [[
                'name' => 'รายการสายสนับสนุน',
                'sum_score' => 100,
                'sequence' => 1,
                'annotation' => null,
                'quantity_main_criterias' => [],
                'quality_main_criterias' => [],
                'support_criterias' => $supportCriterias,
            ]],
        ]],
    ];
}
```

- [ ] **Step 2: Write failing create/show test**

```php
public function test_admin_can_create_and_show_support_criteria_template(): void
{
    $response = $this->postJson(route('report-structure.store'), $this->payload([
        [
            'sequence' => 1,
            'activity_name' => 'พัฒนาระบบบริการ',
            'indicator' => 'งานเสร็จตามแผน',
            'target_value' => 95.5,
            'weight' => 60,
        ],
        [
            'sequence' => 2,
            'activity_name' => 'สนับสนุนผู้ใช้งาน',
            'indicator' => 'แก้ปัญหาภายใน SLA',
            'target_value' => 90,
            'weight' => 40,
        ],
    ]));

    $response->assertCreated()->assertJson(['success' => true]);
    $versionId = $response->json('data.id');

    $this->assertDatabaseHas('support_criterias', [
        'activity_name' => 'พัฒนาระบบบริการ',
        'target_value' => 95.5,
        'weight' => 60,
    ]);

    $this->getJson(route('report-structure.show', $versionId))
        ->assertOk()
        ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.activity_name', 'พัฒนาระบบบริการ')
        ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.target_value', 95.5)
        ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.1.sequence', 2);
}
```

- [ ] **Step 3: Write failing validation test**

```php
public function test_support_template_rejects_invalid_numeric_values(): void
{
    $response = $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => 'งานสนับสนุน',
        'indicator' => 'ตัวชี้วัด',
        'target_value' => -1,
        'weight' => 101,
    ]]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'categories.0.evaluation_lists.0.support_criterias.0.target_value',
        'categories.0.evaluation_lists.0.support_criterias.0.weight',
    ]);
}
```

- [ ] **Step 4: Run create/show and validation tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="can_create_and_show|rejects_invalid"
```

Expected: both FAIL because the controller neither persists/serializes nor validates `support_criterias`.

- [ ] **Step 5: Add request validation**

เพิ่ม rules ชุดเดียวกันใน `store()` และ `update()` หลัง rules ของ evaluation list:

```php
'categories.*.evaluation_lists.*.support_criterias' => 'sometimes|array|min:1',
'categories.*.evaluation_lists.*.support_criterias.*.support_criteria_id' => 'sometimes|nullable|integer|exists:support_criterias,id',
'categories.*.evaluation_lists.*.support_criterias.*.sequence' => 'required|integer|min:1',
'categories.*.evaluation_lists.*.support_criterias.*.activity_name' => 'required|string|max:255',
'categories.*.evaluation_lists.*.support_criterias.*.indicator' => 'required|string',
'categories.*.evaluation_lists.*.support_criterias.*.target_value' => 'required|numeric|min:0',
'categories.*.evaluation_lists.*.support_criterias.*.weight' => 'required|numeric|gt:0|max:100',
```

- [ ] **Step 6: Persist support rows during store**

เพิ่มหลังการสร้าง quality criteria ภายใน evaluation-list loop:

```php
foreach ($evalListData['support_criterias'] ?? [] as $supportData) {
    $evaluationList->supportCriterias()->create([
        'sequence' => $supportData['sequence'],
        'activity_name' => $supportData['activity_name'],
        'indicator' => $supportData['indicator'],
        'target_value' => $supportData['target_value'],
        'weight' => $supportData['weight'],
    ]);
}
```

เพิ่ม relation ใน response load ของ `store()`:

```php
'categories.evaluationLists.supportCriterias',
```

- [ ] **Step 7: Eager load and serialize support rows in show**

เพิ่ม relation ใน `with()` ของ `show()`:

```php
'categories.evaluationLists.supportCriterias' => function ($query) {
    $query->select(
        'id',
        'evaluation_list_id',
        'sequence',
        'activity_name',
        'indicator',
        'target_value',
        'weight'
    )->orderBy('sequence');
},
```

เพิ่ม key ใน formatted evaluation list:

```php
'support_criterias' => $evalList->supportCriterias->map(function ($supportCriteria) {
    return [
        'support_criteria_id' => $supportCriteria->id,
        'sequence' => $supportCriteria->sequence,
        'activity_name' => $supportCriteria->activity_name,
        'indicator' => $supportCriteria->indicator,
        'target_value' => (float) $supportCriteria->target_value,
        'weight' => (float) $supportCriteria->weight,
    ];
})->values()->all(),
```

- [ ] **Step 8: Run create/show and validation tests and verify GREEN**

Run the Step 4 command.

Expected: both PASS.

- [ ] **Step 9: Run Task 2 tests and commit**

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
git add app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: persist support criteria templates"
```

Expected: all support template tests PASS.

---

### Task 3: Update and remove support criteria through the API

**Files:**

- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**

- Consumes: `support_criteria_id` returned by `show()`
- Produces: scoped create/update/delete synchronization per criteria version

- [ ] **Step 1: Write failing update synchronization test**

```php
public function test_admin_can_update_add_reorder_and_remove_support_criteria(): void
{
    $created = $this->postJson(route('report-structure.store'), $this->payload([
        ['sequence' => 1, 'activity_name' => 'เดิมหนึ่ง', 'indicator' => 'ตัวชี้วัดหนึ่ง', 'target_value' => 80, 'weight' => 50],
        ['sequence' => 2, 'activity_name' => 'เดิมสอง', 'indicator' => 'ตัวชี้วัดสอง', 'target_value' => 90, 'weight' => 50],
    ]))->assertCreated();

    $versionId = $created->json('data.id');
    $version = CriteriaVersion::with('categories.evaluationLists.supportCriterias')->findOrFail($versionId);
    $category = $version->categories->first();
    $evaluationList = $category->evaluationLists->first();
    $kept = $evaluationList->supportCriterias->first();
    $removed = $evaluationList->supportCriterias->last();

    $payload = $this->payload([
        [
            'support_criteria_id' => $kept->id,
            'sequence' => 2,
            'activity_name' => 'แก้ไขรายการเดิม',
            'indicator' => 'ตัวชี้วัดใหม่',
            'target_value' => 99,
            'weight' => 70,
        ],
        [
            'sequence' => 1,
            'activity_name' => 'เพิ่มรายการใหม่',
            'indicator' => 'ตัวชี้วัดรายการใหม่',
            'target_value' => 75,
            'weight' => 30,
        ],
    ]);
    $payload['version_name'] = $version->version_name;
    $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
    $payload['categories'][0]['categorie_id'] = $category->id;
    $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

    $this->putJson(route('report-structure.update', $versionId), $payload)
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('support_criterias', [
        'id' => $kept->id,
        'sequence' => 2,
        'activity_name' => 'แก้ไขรายการเดิม',
    ]);
    $this->assertDatabaseHas('support_criterias', [
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 1,
        'activity_name' => 'เพิ่มรายการใหม่',
    ]);
    $this->assertDatabaseMissing('support_criterias', ['id' => $removed->id]);
}
```

- [ ] **Step 2: Write failing uncheck/removal test**

```php
public function test_omitting_support_criteria_on_update_removes_existing_template_rows(): void
{
    $created = $this->postJson(route('report-structure.store'), $this->payload([
        ['sequence' => 1, 'activity_name' => 'ต้องถูกลบ', 'indicator' => 'ตัวชี้วัด', 'target_value' => 80, 'weight' => 100],
    ]))->assertCreated();

    $versionId = $created->json('data.id');
    $version = CriteriaVersion::with(['reportDatas', 'categories.evaluationLists'])->findOrFail($versionId);
    $category = $version->categories->first();
    $evaluationList = $category->evaluationLists->first();
    $payload = $this->payload([]);
    unset($payload['categories'][0]['evaluation_lists'][0]['support_criterias']);
    $payload['version_name'] = $version->version_name;
    $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
    $payload['categories'][0]['categorie_id'] = $category->id;
    $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

    $this->putJson(route('report-structure.update', $versionId), $payload)->assertOk();

    $this->assertDatabaseCount('support_criterias', 0);
}
```

- [ ] **Step 3: Run update and removal tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="can_update_add_reorder|omitting_support"
```

Expected: both FAIL because update ignores `support_criterias` and leaves existing rows untouched.

- [ ] **Step 4: Implement scoped update/create tracking and removal**

เพิ่ม import ใน controller:

```php
use App\Models\SupportCriteria;
```

เพิ่ม tracker ข้าง `$keptQualSubIds`:

```php
$keptSupportCriteriaIds = [];
```

เพิ่มภายใน evaluation-list loop หลัง quality processing:

```php
foreach ($evalListData['support_criterias'] ?? [] as $supportData) {
    $supportCriteriaId = $supportData['support_criteria_id'] ?? null;
    $supportCriteria = $supportCriteriaId
        ? $evaluationList->supportCriterias()->whereKey($supportCriteriaId)->first()
        : null;

    if ($supportCriteriaId && ! $supportCriteria) {
        throw ValidationException::withMessages([
            'support_criterias' => ['ไม่พบรายการเกณฑ์สายสนับสนุนเดิมในรายการประเมินนี้'],
        ]);
    }

    $attributes = [
        'sequence' => $supportData['sequence'],
        'activity_name' => $supportData['activity_name'],
        'indicator' => $supportData['indicator'],
        'target_value' => $supportData['target_value'],
        'weight' => $supportData['weight'],
    ];

    if ($supportCriteria) {
        $supportCriteria->update($attributes);
    } else {
        $supportCriteria = $evaluationList->supportCriterias()->create($attributes);
    }

    $keptSupportCriteriaIds[] = $supportCriteria->id;
}
```

เพิ่ม cleanup ก่อนลบ evaluation lists:

```php
SupportCriteria::whereHas('evaluationList', function ($query) use ($version) {
    $query->where('criteria_version_id', $version->id);
})
    ->when(! empty($keptSupportCriteriaIds), function ($query) use ($keptSupportCriteriaIds) {
        $query->whereNotIn('id', $keptSupportCriteriaIds);
    })
    ->when(empty($keptSupportCriteriaIds), function ($query) {
        $query->whereNotNull('id');
    })
    ->delete();
```

- [ ] **Step 5: Run update and removal tests and verify GREEN**

Run the Step 3 command.

Expected: both PASS.

- [ ] **Step 6: Run Task 3 tests and commit**

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
git add app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: sync support criteria templates"
```

Expected: all support template API tests PASS.

---

### Task 4: Create-page support criteria form

**Files:**

- Create: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Create: `tests/Feature/SupportCriteriaTemplateViewTest.php`
- Modify: `resources/views/criteria_config/partials/create-evaluation-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`

**Interfaces:**

- Produces checkbox: `.support_criteria_type`
- Produces container: `.support_criterias_container`
- Produces repeatable block: `.support_criteria_block[data-draggable-level="support"]`
- Produces inputs: `.support_activity_name`, `.support_indicator`, `.support_target_value`, `.support_weight`
- Produces hidden identity: `.support_criteria_id`
- Produces payload: `evalData.support_criterias`

- [ ] **Step 1: Write failing markup contract test**

Create `tests/Feature/SupportCriteriaTemplateViewTest.php`:

```php
<?php

test('create evaluation template exposes support criteria controls after quality', function () {
    $html = view('criteria_config.partials.create-evaluation-template')->render();

    expect($html)
        ->toContain('quality_criteria_type')
        ->toContain('support_criteria_type')
        ->toContain('เกณฑ์สำหรับสายสนับสนุน')
        ->toContain('support_criterias_container')
        ->toContain('support_activity_name')
        ->toContain('support_indicator')
        ->toContain('support_target_value')
        ->toContain('support_weight')
        ->toContain('คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100');

    expect(strpos($html, 'quality_criteria_type'))
        ->toBeLessThan(strpos($html, 'support_criteria_type'));
});
```

- [ ] **Step 2: Run markup test and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php --filter=create
```

Expected: FAIL because support markup does not exist.

- [ ] **Step 3: Add shared support template markup**

Create `resources/views/criteria_config/partials/support-criteria-template.blade.php` with:

```blade
<div class="support_criterias_container hidden space-y-4 border-l-4 border-amber-400 pl-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h6 class="font-bold text-gray-900">เกณฑ์สำหรับสายสนับสนุน</h6>
        <button type="button" class="add_support_criteria_btn rounded-lg bg-amber-100 px-3 py-1.5 text-sm text-amber-800 hover:bg-amber-200">
            + เพิ่มเกณฑ์สายสนับสนุน
        </button>
    </div>

    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
        คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100
    </p>

    <div class="support_criteria_items space-y-3">
        <div class="support_criteria_block rounded-lg bg-white p-4 shadow-sm" draggable="true" data-draggable-level="support">
            <input type="hidden" class="support_criteria_id" value="">
            <div class="mb-3 flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-gray-700">รายการ <span class="support_sequence">1.1.1</span></span>
                <div class="flex items-center gap-2">
                    <button type="button" class="drag_handle text-gray-500" title="ลากเพื่อจัดลำดับ">⋮⋮</button>
                    <button type="button" class="move_support_up_btn hidden text-blue-600" disabled>↑</button>
                    <button type="button" class="move_support_down_btn hidden text-blue-600">↓</button>
                    <button type="button" class="delete_support_criteria_btn text-red-600">ลบ</button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="text-sm font-medium text-gray-700">
                    กิจกรรม/โครงการ/งาน <span class="text-red-500">*</span>
                    <input type="text" class="support_activity_name mt-2 block w-full rounded-lg border border-gray-300 p-2.5" placeholder="กิจกรรม/โครงการ/งาน">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    ตัวชี้วัด/เกณฑ์การประเมิน <span class="text-red-500">*</span>
                    <input type="text" class="support_indicator mt-2 block w-full rounded-lg border border-gray-300 p-2.5" placeholder="ตัวชี้วัด/เกณฑ์การประเมิน">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    ระดับค่าเป้าหมาย <span class="text-red-500">*</span>
                    <input type="number" min="0" step="0.01" class="support_target_value mt-2 block w-full rounded-lg border border-gray-300 p-2.5" placeholder="ระดับค่าเป้าหมาย">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    น้ำหนัก <span class="text-red-500">*</span>
                    <input type="number" min="0.01" max="100" step="0.01" class="support_weight mt-2 block w-full rounded-lg border border-gray-300 p-2.5" placeholder="น้ำหนัก">
                </label>
            </div>
        </div>
    </div>
</div>
```

เพิ่ม Checkbox หลัง label คุณภาพใน `create-evaluation-template.blade.php`:

```blade
<label class="flex items-center">
    <input name="criteria_type_support" type="checkbox"
        class="criteria_type support_criteria_type form-checkbox h-5 w-5 rounded text-amber-600 focus:ring-amber-500"
        value="support">
    <span class="ml-2 text-sm">เกณฑ์สำหรับสายสนับสนุน</span>
</label>
```

เพิ่ม include หลัง quality template:

```blade
@include('criteria_config.partials.support-criteria-template')
```

- [ ] **Step 4: Run markup test and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 5: Write failing create-script contract test**

เพิ่ม test:

```php
test('create script toggles collects and reorders support criteria', function () {
    $script = file_get_contents(resource_path('views/criteria_config/partials/create-script.blade.php'));

    expect($script)
        ->toContain("querySelector('.support_criteria_type')")
        ->toContain("querySelector('.support_criterias_container')")
        ->toContain("querySelectorAll('.support_criteria_block')")
        ->toContain('evalData.support_criterias = []')
        ->toContain('support_criteria_id')
        ->toContain('support_activity_name')
        ->toContain('support_target_value')
        ->toContain('support_weight');
});
```

- [ ] **Step 6: Run script test and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php --filter=create_script
```

Expected: FAIL because create script has no support hooks.

- [ ] **Step 7: Implement Create JavaScript behavior**

แก้ `create-script.blade.php` ดังนี้:

1. ใน criteria-type `change` handler เพิ่ม:

```javascript
const supportContainer = evalBlock.querySelector('.support_criterias_container');
const supportCheckbox = evalBlock.querySelector('.support_criteria_type');
supportContainer.classList.toggle('hidden', !supportCheckbox.checked);
```

2. ใน `cloneAndClear()` เพิ่ม support selectors:

```javascript
node.querySelectorAll('.support_criteria_id').forEach((input) => input.value = '');
node.querySelectorAll('.support_criteria_block:not(:first-child)').forEach((block) => block.remove());

if (blockSelector === '.evaluation_list_block') {
    node.querySelector('.support_criterias_container').classList.add('hidden');
}
```

3. เพิ่ม sequence helper:

```javascript
function updateSupportSequence(container, evalPrefix = '') {
    container.querySelectorAll('.support_criteria_block').forEach((block, index) => {
        setSequenceInputValue(block.querySelector('.support_sequence'), `${evalPrefix}.${index + 1}`);
    });
    updateButtonStates('.support_criteria_block', '.move_support_up_btn', '.move_support_down_btn');
}
```

เรียก helper นี้จาก `refreshOrderUI()`/sequence refresh ของแต่ละ evaluation list

4. เพิ่ม delegated click actions:

```javascript
if (e.target.closest('.add_support_criteria_btn')) {
    const evaluationBlock = e.target.closest('.evaluation_list_block');
    const items = evaluationBlock.querySelector('.support_criteria_items');
    const block = cloneAndClear('.support_criteria_block');
    items.appendChild(block);
    refreshOrderUI();
    markDirty();
}

if (e.target.closest('.delete_support_criteria_btn')) {
    const block = e.target.closest('.support_criteria_block');
    const items = block.closest('.support_criteria_items');
    if (items.querySelectorAll('.support_criteria_block').length === 1) {
        showValidationErrorModal('ต้องมีเกณฑ์สายสนับสนุนอย่างน้อย 1 รายการ');
    } else {
        block.remove();
        refreshOrderUI();
        markDirty();
    }
}

if (e.target.closest('.move_support_up_btn')) {
    const block = e.target.closest('.support_criteria_block');
    const previous = block.previousElementSibling;
    if (previous?.classList.contains('support_criteria_block')) {
        block.parentNode.insertBefore(block, previous);
        refreshOrderUI();
        markDirty();
    }
}

if (e.target.closest('.move_support_down_btn')) {
    const block = e.target.closest('.support_criteria_block');
    const next = block.nextElementSibling;
    if (next?.classList.contains('support_criteria_block')) {
        block.parentNode.insertBefore(next, block);
        refreshOrderUI();
        markDirty();
    }
}
```

5. ไม่เพิ่ม key สายสนับสนุนใน `evalData` เริ่มต้น เพื่อให้ Checkbox ที่ไม่ถูกเลือกไม่ส่งข้อมูล เมื่อ Checkbox ถูกเลือกให้เริ่ม array ก่อน collect:

```javascript
evalData.support_criterias = [];
```

6. หลัง collect quality เพิ่ม:

```javascript
if (evalBlock.querySelector('.support_criteria_type').checked) {
    evalData.support_criterias = [];
    Array.from(evalBlock.querySelectorAll('.support_criteria_block')).forEach((supportBlock, supportIndex) => {
        const activityName = supportBlock.querySelector('.support_activity_name').value.trim();
        const indicator = supportBlock.querySelector('.support_indicator').value.trim();
        const targetValue = supportBlock.querySelector('.support_target_value').value;
        const weight = supportBlock.querySelector('.support_weight').value;

        if (!activityName || !indicator || targetValue === '' || weight === '') {
            throw new Error(`กรุณากรอกข้อมูลเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ให้ครบถ้วน`);
        }
        if (Number(targetValue) < 0) {
            throw new Error(`ระดับค่าเป้าหมายของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องไม่ติดลบ`);
        }
        if (Number(weight) <= 0 || Number(weight) > 100) {
            throw new Error(`น้ำหนักของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องมากกว่า 0 และไม่เกิน 100`);
        }

        evalData.support_criterias.push({
            sequence: supportIndex + 1,
            activity_name: activityName,
            indicator,
            target_value: Number(targetValue),
            weight: Number(weight),
        });
    });
}
```

- [ ] **Step 8: Run Create view tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php
```

Expected: Create markup and script tests PASS.

- [ ] **Step 9: Commit Task 4**

```powershell
git add resources/views/criteria_config/partials/create-evaluation-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/support-criteria-template.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php
git commit -m "feat: add support criteria create template"
```

---

### Task 5: Edit-page load, update, and removal behavior

**Files:**

- Create: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/edit-evaluation-template.blade.php`
- Modify: `resources/views/criteria_config/partials/edit-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-evaluation-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-sequence-updaters.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`

**Interfaces:**

- Consumes: support markup hooks from Task 4
- Consumes: `evalData.support_criterias` from API `show()`
- Produces: edit payload with optional `support_criteria_id`

- [ ] **Step 1: Write failing Edit contract test**

เพิ่ม test:

```php
test('edit template loads toggles and collects support criteria', function () {
    $template = view('criteria_config.partials.edit-evaluation-template')->render();
    $editScript = file_get_contents(resource_path('views/criteria_config/partials/edit-script.blade.php'));
    $criteriaHandler = file_get_contents(resource_path('views/criteria_config/partials/script-edit-criteria-type-handler.blade.php'));
    $populate = file_get_contents(resource_path('views/criteria_config/partials/script-edit-populate-helpers.blade.php'));
    $collect = file_get_contents(resource_path('views/criteria_config/partials/script-edit-collect-form-data.blade.php'));

    expect($template)
        ->toContain('support_criteria_type')
        ->toContain('support_criterias_container');
    expect($editScript)->toContain("@include('criteria_config.partials.script-edit-support-handlers')");
    expect($criteriaHandler)->toContain("querySelector('.support_criteria_type')");
    expect($populate)
        ->toContain('evalData.support_criterias')
        ->toContain('populateSupportCriteria');
    expect($collect)
        ->toContain('evalData.support_criterias = []')
        ->toContain('support_criteria_id');
});
```

- [ ] **Step 2: Run Edit contract test and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php --filter=edit_template
```

Expected: FAIL because Edit has no support hooks.

- [ ] **Step 3: Add support markup to Edit**

เพิ่ม Checkbox เดียวกับ Task 4 หลัง quality Checkbox และ include ต่อจาก quality container ใน `edit-evaluation-template.blade.php`:

```blade
<label class="flex items-center">
    <input name="criteria_type_support" type="checkbox"
        class="criteria_type support_criteria_type form-checkbox h-5 w-5 rounded text-amber-600 focus:ring-amber-500"
        value="support">
    <span class="ml-2 text-sm">เกณฑ์สำหรับสายสนับสนุน</span>
</label>

@include('criteria_config.partials.support-criteria-template')
```

- [ ] **Step 4: Add Edit handlers**

Create `script-edit-support-handlers.blade.php`:

```javascript
function populateSupportCriteria(container, supportData) {
    const template = document.querySelector('.support_criteria_block');
    container.querySelectorAll('.support_criteria_block').forEach((block) => block.remove());

    supportData.forEach((item) => {
        const block = template.cloneNode(true);
        block.querySelector('.support_criteria_id').value = item.support_criteria_id || item.id || '';
        block.querySelector('.support_activity_name').value = item.activity_name || '';
        block.querySelector('.support_indicator').value = item.indicator || '';
        block.querySelector('.support_target_value').value = item.target_value ?? '';
        block.querySelector('.support_weight').value = item.weight ?? '';
        container.querySelector('.support_criteria_items').appendChild(block);
    });
}

function addSupportCriteria(evaluationBlock) {
    const container = evaluationBlock.querySelector('.support_criteria_items');
    const template = document.querySelector('.support_criteria_block');
    const block = template.cloneNode(true);
    clearIdentityAttributes(block);
    block.querySelectorAll('input').forEach((input) => input.value = '');
    container.appendChild(block);
    updateSequences();
    markDirty();
}

function deleteSupportCriteria(block) {
    const container = block.closest('.support_criteria_items');
    if (container.querySelectorAll('.support_criteria_block').length === 1) {
        showError('ต้องมีเกณฑ์สายสนับสนุนอย่างน้อย 1 รายการ');
        return;
    }
    block.remove();
    updateSequences();
    markDirty();
}
```

Include ใน `edit-script.blade.php` ก่อน populate helpers:

```blade
@include('criteria_config.partials.script-edit-support-handlers')
```

เพิ่ม actions ใน click delegation ของ `script-edit-event-listeners.blade.php`:

```javascript
if (e.target.closest('.add_support_criteria_btn')) {
    addSupportCriteria(e.target.closest('.evaluation_list_block'));
}
if (e.target.closest('.delete_support_criteria_btn')) {
    deleteSupportCriteria(e.target.closest('.support_criteria_block'));
}
```

- [ ] **Step 5: Extend show/hide, clone, and sequence logic**

ใน `handleCriteriaTypeChange()` เพิ่ม:

```javascript
const supportContainer = evaluationBlock.querySelector('.support_criterias_container');
const supportCheckbox = evaluationBlock.querySelector('.support_criteria_type');
supportContainer.classList.toggle('hidden', !supportCheckbox.checked);
```

ใน `script-edit-evaluation-handlers.blade.php` เพิ่มใน `handleAddEvaluation()`:

```javascript
newBlock.querySelector('.support_criterias_container')?.classList.add('hidden');
newBlock.querySelectorAll('.support_criteria_block:not(:first-child)').forEach((block) => block.remove());
```

ใน `script-edit-clone-helpers.blade.php`:

```javascript
'data-support-criteria-id',
```

เพิ่ม `.support_criteria_id` ใน selector ที่ล้าง hidden IDs:

```javascript
rootElement.querySelectorAll(
    '.category_id_value, .evaluation_id_value, .quantity_main_id_value, .quality_main_id_value, .quant_sub_criteria_id, .support_criteria_id'
).forEach((element) => {
    element.value = '';
});
```

เพิ่ม `.support_criteria_block:not(:first-child)` ใน selector ที่ลบ clone ซ้ำ:

```javascript
rootElement.querySelectorAll('.support_criteria_block:not(:first-child)').forEach((block) => block.remove());
```

ใน `updateSequences()` เพิ่ม:

```javascript
evalBlock.querySelectorAll('.support_criteria_block').forEach((supportBlock, supportIndex) => {
    setSequenceInputValue(supportBlock.querySelector('.support_sequence'), `${evalPrefix}.${supportIndex + 1}`);
});
```

- [ ] **Step 6: Populate Edit data**

ใน `createEvaluationFromData()` เพิ่ม:

```javascript
const hasSupport = Array.isArray(evalData.support_criterias) && evalData.support_criterias.length > 0;
const supportCheckbox = newBlock.querySelector('.support_criteria_type');
const supportContainer = newBlock.querySelector('.support_criterias_container');

supportCheckbox.checked = hasSupport;
supportContainer.classList.toggle('hidden', !hasSupport);

if (hasSupport) {
    populateSupportCriteria(supportContainer, evalData.support_criterias);
}
```

- [ ] **Step 7: Collect and validate Edit payload**

ไม่เพิ่ม key สายสนับสนุนใน `evalData` เริ่มต้น แล้วสร้าง array และ collect เฉพาะเมื่อ Checkbox ถูกเลือก:

```javascript
if (evalBlock.querySelector('.support_criteria_type').checked) {
    evalData.support_criterias = [];
    Array.from(evalBlock.querySelectorAll('.support_criteria_block')).forEach((supportBlock, supportIndex) => {
        const activityName = supportBlock.querySelector('.support_activity_name').value.trim();
        const indicator = supportBlock.querySelector('.support_indicator').value.trim();
        const targetValue = supportBlock.querySelector('.support_target_value').value;
        const weight = supportBlock.querySelector('.support_weight').value;

        if (!activityName || !indicator || targetValue === '' || weight === '') {
            throw new Error(`กรุณากรอกข้อมูลเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ให้ครบถ้วน`);
        }
        if (Number(targetValue) < 0) {
            throw new Error(`ระดับค่าเป้าหมายของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องไม่ติดลบ`);
        }
        if (Number(weight) <= 0 || Number(weight) > 100) {
            throw new Error(`น้ำหนักของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องมากกว่า 0 และไม่เกิน 100`);
        }

        const supportCriteriaId = supportBlock.querySelector('.support_criteria_id').value;
        const supportPayload = {
            sequence: supportIndex + 1,
            activity_name: activityName,
            indicator,
            target_value: Number(targetValue),
            weight: Number(weight),
        };
        if (supportCriteriaId) {
            supportPayload.support_criteria_id = Number(supportCriteriaId);
        }
        evalData.support_criterias.push(supportPayload);
    });
}
```

การยกเลิก Checkbox จะไม่ส่ง key `support_criterias` และ API cleanup จะลบรายการเดิม

- [ ] **Step 8: Run Edit contract test and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 9: Run all focused tests**

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/CriteriaConfigControlsTest.php
```

Expected: all tests PASS.

- [ ] **Step 10: Commit Task 5**

```powershell
git add resources/views/criteria_config/partials/edit-evaluation-template.blade.php resources/views/criteria_config/partials/edit-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php resources/views/criteria_config/partials/script-edit-evaluation-handlers.blade.php resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php resources/views/criteria_config/partials/script-edit-sequence-updaters.blade.php resources/views/criteria_config/partials/script-edit-event-listeners.blade.php resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php
git commit -m "feat: edit support criteria templates"
```

---

### Task 6: Regression and delivery verification

**Files:**

- Verify only; modify production files only if a failing regression demonstrates a defect introduced by Tasks 1–5

**Interfaces:**

- Verifies: migration, API, Create/Edit UI contracts, legacy report structures, frontend build

- [ ] **Step 1: Format changed PHP files**

```powershell
vendor\bin\pint app\Models\SupportCriteria.php app\Models\EvaluationList.php app\Http\Controllers\ReportStructureController.php database\migrations\2026_07_20_000001_create_support_criterias_table.php tests\Feature\Report\SupportCriteriaTemplateTest.php tests\Feature\SupportCriteriaTemplateViewTest.php
```

Expected: Pint exits 0.

- [ ] **Step 2: Run focused backend and view tests**

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/Report/ReportStructureTest.php tests/Feature/CriteriaConfigControlsTest.php
```

Expected: all focused tests PASS with no errors.

- [ ] **Step 3: Run complete PHP suite**

```powershell
php artisan test
```

Expected: complete suite PASS. If an unrelated pre-existing failure occurs, record the exact test and confirm it also fails without the feature diff before reporting it as pre-existing.

- [ ] **Step 4: Build frontend assets**

```powershell
npm run build
```

Expected: Vite build exits 0 with no compile error.

- [ ] **Step 5: Check migrations and diff hygiene**

```powershell
php artisan migrate:fresh --env=testing --force
git diff --check
git status --short
```

Expected: migrations complete, `git diff --check` exits 0, and status contains no accidental changes outside the feature plus the user's pre-existing unrelated files.

- [ ] **Step 6: Final implementation commit if formatting changed files**

```powershell
git add app/Models/SupportCriteria.php app/Models/EvaluationList.php app/Http/Controllers/ReportStructureController.php database/migrations/2026_07_20_000001_create_support_criterias_table.php resources/views/criteria_config/partials/create-evaluation-template.blade.php resources/views/criteria_config/partials/edit-evaluation-template.blade.php resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/edit-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php resources/views/criteria_config/partials/script-edit-evaluation-handlers.blade.php resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php resources/views/criteria_config/partials/script-edit-sequence-updaters.blade.php resources/views/criteria_config/partials/script-edit-event-listeners.blade.php resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php
git diff --cached --quiet; if ($LASTEXITCODE -ne 0) { git commit -m "style: format support criteria template" }
```

Expected: commit created only when formatting or verification required tracked changes.
