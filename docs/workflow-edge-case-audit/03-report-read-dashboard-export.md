# Epic C — Report Read Model, Dashboard, Export

## Workflow ที่เกี่ยวข้อง

1. Role dashboard แสดงงานที่ต้องทำและสถานะ
2. Admin dashboard รวม read model จาก assignments/reports/scores
3. Report detail แสดงคะแนน หลักฐาน และ comment
4. Export สร้าง Excel/report จากข้อมูลเดียวกัน
5. Graph/status summary แสดงภาพรวมการประเมิน

## Module / Interface ที่เกี่ยวข้อง

- `ReportDataService`
- `AdminDashboardQuery`
- `AdminDashboardStatusSummary`
- `ScoreService`
- `EvaluationScoreSummary`
- `ReportsExport`
- `SingleReportExport`
- `GraphDataService`
- `EvaluationService`
- `Reports`

## C-01 — Total score ไม่เป็น source of truth เดียว

Severity: `P1`

### จุดที่พบ

- `app/Support/EvaluationScoreSummary.php`
- `app/Services/ScoreService.php`
- `app/Exports/ReportsExport.php`
- `app/Services/SupportScoreService.php`

### การทำงานปัจจุบัน

บางหน้ารวม support score ใน total บางหน้าคำนวณจาก quantity+quality เป็นหลัก และบางจุด cap support total ที่ 100 ขณะที่ backend persist uncapped total

### สาเหตุของ defect

ไม่มี `ReportScoreReadModel` Module ที่เป็นเจ้าของนิยามคะแนนรวม Interface เดียว Dashboard, detail และ export ต่างคำนวณเอง

### Failure mode

ผู้ใช้เห็นคะแนนรวมต่างกันระหว่าง:

- report detail
- dashboard
- export
- support table UI

ทำให้เกิด dispute ว่าคะแนนจริงคือค่าไหน

### Coverage gap

มี test ว่า support total uncapped ได้ แต่ยังไม่มี cross-read-model consistency test

### แนวทางแก้

สร้าง `ReportScoreReadModel`:

- `quantityTotal`
- `qualityTotal`
- `supportTotalRaw`
- `supportTotalDisplay`
- `grandTotal`
- `grandTotalPolicy`

ให้ทุกหน้าจอ/export อ่านจาก Module เดียว

## C-02 — Comment มีสองแหล่งความจริง

Severity: `P1`

### จุดที่พบ

- `app/Models/Reports.php`
- role score controllers

### การทำงานปัจจุบัน

ระบบมี comment แยก:

- `evaluator_comment`
- `director_comment`
- `manager_comment`
- `comment`

แต่ controller ของแต่ละ role เขียน role-specific comment แล้วเขียนทับ `report.comment` ด้วย comment ล่าสุด

### สาเหตุของ defect

มี `Reports::syncCombinedComment()` แต่ controller ไม่ใช้ จึงทำให้ `comment` เป็น mutable mirror ของบทบาทล่าสุด ไม่ใช่ combined read model

### Failure mode

dashboard/export ที่อ่าน `report.comment` อาจเห็นแค่ comment ล่าสุด และสูญเสียบริบทจากบทบาทก่อนหน้า

### Coverage gap

ยังไม่มี final export/dashboard test ที่ assert ว่าทุก role comment ถูก preserve

### แนวทางแก้

ให้ `Reports` หรือ `ReportCommentReadModel` เป็น Module กลาง:

- role comments เป็น write model
- combined comment เป็น derived read model
- controller ห้ามเขียน `comment` โดยตรง

## C-03 — ReportDataService assume assignment graph สมบูรณ์

Severity: `P1`

### จุดที่พบ

- `app/Services/ReportDataService.php`
- method: `getReportData`

### การทำงานปัจจุบัน

เมื่อ load report แล้ว code dereference `$assignment->assignmentData` และ relation ต่อเนื่องทันที

### สาเหตุของ defect

Interface ของ `getReportData` ไม่ระบุ error mode สำหรับ orphan report หรือ report ที่ไม่ได้สร้างผ่าน assignment workflow

### Failure mode

ถ้ามี report orphan หรือ data drift ระบบอาจ runtime error แทนที่จะคืน 404/403 หรือ read-only empty state

### Coverage gap

test ส่วนใหญ่สร้าง assignment graph ครบ จึงไม่จับ orphan scenario

### แนวทางแก้

แยก `ReportReadModel` Module:

- validate graph completeness
- return typed error state
- view/export ไม่ dereference relation โดยตรง

## C-04 — EvaluationService parse optional dates แบบไม่ guard

Severity: `P1`

### จุดที่พบ

- `app/Services/EvaluationService.php`
- methods:
  - `filterEvaluations`
  - `sortEvaluations`

### การทำงานปัจจุบัน

มีการเรียก `Carbon::parse` กับค่า date ที่ optional หรืออาจ malformed

### สาเหตุของ defect

Date parsing อยู่ใน filter/sort Implementation โดยไม่มี Adapter สำหรับ safe date normalization

### Failure mode

assignment ที่มีวันที่ว่างหรือผิดรูปแบบ อาจทำให้ dashboard/list ทั้งหน้าล้ม

### Coverage gap

ไม่มี test สำหรับ bad/missing date ใน evaluation listing

### แนวทางแก้

สร้าง safe date parser:

- คืน `null` เมื่อ parse ไม่ได้
- filter/sort ต้อง fallback deterministic
- log data-quality issue แต่ไม่ทำให้หน้าล้ม

## C-05 — Manager_assign ถูกนับไม่ตรงกันใน dashboard/read model

Severity: `P1`

### จุดที่พบ

- `AdminDashboardStatusSummary`
- `AdminDashboardQuery`
- `GraphDataService`
- dashboard metadata ของแต่ละ role

### การทำงานปัจจุบัน

`Manager_assign` ถูกจัดกลุ่มต่างกันตาม Module บางจุดเหมือน not-started บางจุดเหมือน in-review

### สาเหตุของ defect

status grouping เป็น shallow Module หลายตัว แทนที่จะเป็น Interface เดียวของ `EvaluationFlow`

### Failure mode

ตัวเลข summary, progress, follow-up และ graph ไม่ตรงกัน

### Coverage gap

test เดิม lock current behavior บางส่วน แต่ไม่ assert cross-module consistency

### แนวทางแก้

ย้าย status grouping เข้า `EvaluationFlow::statusMeta(status)` แล้วให้ทุก read model ใช้ร่วมกัน
