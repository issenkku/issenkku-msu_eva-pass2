# Epic A — Assignment และ Evaluation Flow

## Workflow ที่เกี่ยวข้อง

1. Admin สร้าง assignment รอบประเมิน
2. ระบบสร้าง `Reports` และ `Assignments` ตามผู้รับการประเมิน
3. Evaluatee กรอกข้อมูลในสถานะ `Assigned` / `Draft`
4. Evaluatee submit แล้วระบบเปลี่ยน status ไปยัง reviewer แรก
5. Evaluator / Director / Manager ตรวจและ submit ต่อไปตาม `evaluation_flow`
6. Scheduler ปรับสถานะเมื่อถึงวันสิ้นสุดรอบ
7. Dashboard อ่านสถานะเพื่อสรุป progress และ follow-up

## Module / Interface ที่เกี่ยวข้อง

- `AssignmentDataController`
- `AssignmentFlow`
- `UpdateReportStatuses`
- `EvaluationScoreController`
- `EvaluatorScoreController`
- `DirectorScoreController`
- `ManagerScoreController`
- `AdminDashboardStatusSummary`
- `AdminDashboardQuery`
- `GraphDataService`

## A-01 — Assignment update อาจลบ report graph เดิม

Severity: `P0`

### จุดที่พบ

- `app/Http/Controllers/AssignmentDataController.php`
- method: `update`

### การทำงานปัจจุบัน

เมื่อ admin update assignment ระบบลบ `Assignments` และ `Reports` เดิม แล้วสร้างชุดใหม่จากข้อมูลล่าสุด

### สาเหตุของ defect

Interface ของ `AssignmentDataController::update` ไม่ได้แยกเจตนาว่า update นี้คือ:

- แก้ metadata ของรอบประเมิน
- แก้รายชื่อผู้ประเมินในรอบที่ยังไม่เริ่ม
- rewrite รอบประเมินทั้งหมด
- retire/copy round

Implementation จึงใช้แนวทางลบแล้วสร้างใหม่โดยไม่มี invariant ป้องกันรายงานที่มีข้อมูลประเมินแล้ว

### Failure mode

ถ้า assignment มี report ที่เริ่มกรอกแล้ว การ update อาจลบ:

- quantity scores
- quality scores
- support scores
- evidence answers
- workload entries
- score histories
- role comments

ผลลัพธ์คือ data loss โดยผู้ใช้เข้าใจว่าแค่แก้ assignment metadata

### Coverage gap

มี CRUD test สำหรับ update assignment แต่ยังไม่มี test กรณี:

- update assignment ที่มี report status ไม่ใช่เริ่มต้น
- update assignment ที่มี scores/evidence/workload/history แล้ว
- update assignment ของ completed report

### แนวทางแก้

Deepen `AssignmentLifecycle` Module ให้มี Interface แยก operation ชัดเจน:

- `updateDraftRound`
- `copyRound`
- `retireRound`
- `changeReviewersBeforeScoring`
- `rejectMutationWhenReportsHaveScores`

Locality ของกติกา data preservation จะอยู่ใน Module เดียว แทนการกระจายใน controller

## A-02 — Status taxonomy แตกหลาย Implementation

Severity: `P1`

### จุดที่พบ

- `app/Support/AdminDashboardStatusSummary.php`
- `app/Support/AdminDashboardQuery.php`
- `app/Services/GraphDataService.php`
- `app/Support/AssignmentFlow.php`
- `app/Console/Commands/UpdateReportStatuses.php`
- `app/Http/Controllers/QualityScoreController.php`
- `app/Http/Controllers/ReportController.php`

### การทำงานปัจจุบัน

ระบบมี status หลายรูปแบบ เช่น:

- `Assigned`
- `Draft`
- `Pending`
- `Evaluator_draft`
- `Director_assigned`
- `Director_draft`
- `Manager_assign`
- `Manager_draft`
- `Completed`
- `pending`
- `ASSIGNED`
- `DRAFT`
- `PENDING`
- `COMPLETED`

### สาเหตุของ defect

Status vocabulary ไม่มี Interface กลาง แต่ถูก hard-code ในหลาย Module:

- scheduler hard-code `Draft/Assigned → Pending`
- dashboard group status เอง
- graph service group status เอง
- role controllers validate และ map status เอง
- `QualityScoreController::store` ใช้ `pending` ตัวเล็ก
- `ReportController::store/update` ใช้ uppercase แต่ workflow หลักใช้ camel-case

### Failure mode

- report ที่เกิดจาก path หนึ่งอาจเข้า workflow อื่นไม่ได้
- dashboard count/progress ไม่ตรงกับ role dashboard
- `Manager_assign` ถูกมองเป็น not-started/in-review ต่างกันตามหน้า
- custom `evaluation_flow` ที่เริ่มจาก director/manager ถูก scheduler บังคับเข้า `Pending`
- API update report อาจ reject status ที่ path อื่นถือว่าถูกต้อง

### Coverage gap

มี test ตาม role-specific happy path แต่ยังไม่มี contract test ว่า status เดียวกันถูกตีความเหมือนกันทุก Module

### แนวทางแก้

Deepen `EvaluationFlow` Module:

- Interface รับ action เช่น `submitFromEvaluatee`, `submitFromEvaluator`, `expireDraft`, `statusGroupForDashboard`
- Implementation เป็นเจ้าของ status vocabulary ทั้งหมด
- ทุก controller, scheduler, dashboard, graph ใช้ Module เดียวกัน

## A-03 — Authorization ของ evaluator กว้างกว่าผู้ถูก assign จริง

Severity: `P0/P1` ขึ้นกับ policy ที่ต้องยืนยัน

### จุดที่พบ

- `app/Services/ReportDataService.php`
- method: `getEvaluatorAssignmentForUser`

### การทำงานปัจจุบัน

evaluator access ผ่านเมื่อ:

- `assignmentData.evaluator_id = user.id`
- หรือ `assignmentData.evaluator_position_id = user.position_id`

### สาเหตุของ defect

Interface ของ authorization ผสมสอง Adapter:

- named actor adapter
- position adapter

โดยไม่มี policy กลางระบุว่า position-based assignment หมายถึง “ทุกคนในตำแหน่งนั้น” หรือ “fallback เมื่อไม่มี user ระบุ”

### Failure mode

ผู้ใช้ที่มีตำแหน่งเดียวกับ evaluator แต่ไม่ได้ถูกเลือกโดยตรงอาจเข้าถึง report ได้ ขณะที่ director/manager path ใช้กติกา explicit user ต่างกัน

### Coverage gap

ยังไม่มี test กรณี:

- user A เป็น evaluator_id
- user B มี position เดียวกับ user A
- user B พยายามเข้า report

### แนวทางแก้

ให้ `EvaluationFlow` หรือ `ReviewerAuthorization` เป็น Module กลางที่ระบุ Interface ชัด:

- `canView(report, actor, stage)`
- `canEdit(report, actor, stage)`
- `assignedReviewerFor(stage)`

## A-04 — Scheduler bypass custom evaluation flow

Severity: `P1`

### จุดที่พบ

- `app/Console/Commands/UpdateReportStatuses.php`
- `app/Support/AssignmentFlow.php`

### การทำงานปัจจุบัน

เมื่อถึงวันสิ้นสุด ระบบเปลี่ยน `Draft` / `Assigned` เป็น `Pending`

### สาเหตุของ defect

Scheduler ไม่ผ่าน `AssignmentFlow::statusForStage` หรือ `AssignmentFlow::stagesFor` จึงไม่รู้ว่า reviewer stage แรกใน assignment คืออะไร

### Failure mode

ถ้า config flow ไม่มี evaluator หรือเริ่มจาก director ระบบจะยังเปลี่ยนเป็น `Pending` ทำให้รายงานเข้าหน้า evaluator ทั้งที่ไม่มี evaluator stage

### Coverage gap

มี test deadline default path แต่ยังไม่มี test custom `evaluation_flow`

### แนวทางแก้

ให้ scheduler เรียก `EvaluationFlow::firstReviewStatus($assignmentData)` แทน hard-code `Pending`

