# Epic B — Score Persistence และ Evidence

## Workflow ที่เกี่ยวข้อง

1. Evaluatee บันทึก quantity/quality/support score และ evidence
2. Evaluatee submit ไปยัง reviewer แรก
3. Reviewer แต่ละบทบาทแก้คะแนนและบันทึกเหตุผล
4. ระบบบันทึก score histories
5. ระบบคำนวณ support score total
6. ระบบแสดง evidence ใน report detail และ role dashboard

## Module / Interface ที่เกี่ยวข้อง

- `EvaluationScoreController`
- `EvaluatorScoreController`
- `DirectorScoreController`
- `ManagerScoreController`
- `ReportController`
- `SupportScoreService`
- `QuantityScoreHistoryRecorder`
- `EvidenceAnswer`
- `ReportDataService`

## B-01 — ReportController endpoint hard 500 จาก missing method

Severity: `P0`

### จุดที่พบ

- `app/Http/Controllers/ReportController.php`
- methods:
  - `addQuantityScores`
  - `updateQuantityScores`
  - `addQualityScores`
  - `updateQualityScores`
  - `addEvidenceAnswers`
  - `updateEvidenceAnswers`

### การทำงานปัจจุบัน

แต่ละ method เรียก:

```php
$this->checkReportEditableStatus($report, ...)
```

แต่ `ReportController` ไม่มี method `checkReportEditableStatus`

### สาเหตุของ defect

มีการ copy pattern จาก role score controllers มาไว้ใน `ReportController` แต่ไม่ได้ย้าย Interface หรือ Implementation ของ editability check มาด้วย

### Failure mode

เมื่อเรียก endpoint เหล่านี้ PHP จะ throw fatal error และ response เป็น HTTP 500 ก่อน validation ทางธุรกิจ

### Coverage gap

ยังไม่มี feature test สำหรับ endpoint ชุด `reports/*/quantity-scores`, `quality-scores`, `evidence-answers`

### แนวทางแก้

ไม่ควร patch ด้วย method local เฉย ๆ ถ้ายังไม่มี status policy กลาง ควรให้ endpoint เหล่านี้เรียก `EvaluationFlow` / `ReportEditPolicy` Module เดียวกับ role controllers

## B-02 — Full replace score semantics ทำให้ partial payload ลบข้อมูล

Severity: `P0`

### จุดที่พบ

- `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- `app/Http/Controllers/EvaluatorScoreController.php`
- `app/Http/Controllers/Director/DirectorScoreController.php`
- `app/Http/Controllers/Manager/ManagerScoreController.php`

### การทำงานปัจจุบัน

ก่อน save score ระบบลบ score เดิมทั้งหมดของ report:

- `QuantityScore::where('report_id', ...)->delete()`
- `QualityScore::where('report_id', ...)->delete()`

จากนั้นสร้างใหม่จาก payload

### สาเหตุของ defect

Interface ของ “save score” ไม่ระบุว่าเป็น:

- full replace
- patch เฉพาะ field ที่ส่งมา
- merge preserve missing values
- clear intentionally

Implementation แต่ละ controller จึง treat payload เป็น full source of truth

### Failure mode

ถ้า frontend ไม่ส่งบาง field เพราะ hidden, disabled, readonly, network issue หรือ form composition ผิด ข้อมูลเดิมจะหายทันที

### Coverage gap

test ส่วนใหญ่ส่ง full payload จึงไม่จับ partial payload data loss

### แนวทางแก้

สร้าง `ScorePersistence` Module:

- Interface: `persist(report, actor, role, action, payload, mode)`
- mode ชัดเจน: `patch`, `replace`, `clear`
- reject partial payload ที่ไม่ครบเมื่อ mode เป็น replace
- เก็บ transaction/history/evidence rules ใน Implementation เดียว

## B-03 — Quantity score semantics ต่างกันระหว่าง evaluatee และ reviewer

Severity: `P1`

### จุดที่พบ

- `EvaluationScoreController::storeEvaluationScores`
- `EvaluatorScoreController::storeEvaluatorScores`
- `DirectorScoreController::storeDirectorScores`
- `ManagerScoreController::storeManagerScores`

### การทำงานปัจจุบัน

Evaluatee path เก็บ quantity row ได้ถ้า `score_C = null` แต่มี `description`

Reviewer path ข้าม row ถ้า `score_C = null`

### สาเหตุของ defect

แต่ละ role controller มี Implementation การ normalize quantity item เอง โดยไม่มี Interface กลางบอกว่า description-only row เป็นข้อมูลที่ต้อง preserve หรือไม่

### Failure mode

ข้อมูล description-only ที่ evaluatee บันทึกไว้ อาจหายเมื่อ reviewer save ต่อ

### Coverage gap

ยังไม่มี cross-role regression test สำหรับ description-only quantity score

### แนวทางแก้

ย้าย normalization เข้า `ScorePersistence` หรือ `QuantityScoreDraft` Module แล้วใช้กติกาเดียวกันทุกบทบาท

## B-04 — Evidence ปะปนเพราะ table เดียวแต่ purpose ไม่ชัด

Severity: `P1`

### จุดที่พบ

- `app/Models/EvidenceAnswer.php`
- `app/Services/ReportDataService.php`
- `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`
- `app/Services/SupportScoreService.php`

### การทำงานปัจจุบัน

`EvidenceAnswer` เก็บหลายชนิดในตารางเดียว:

- quantity/quality evidence
- support evidence
- workload evidence

แยก purpose ด้วย nullable columns เช่น `support_criteria_id`, `workload_entry_id`, `quality_main_criteria_id`

### สาเหตุของ defect

Interface ของ evidence เป็น shallow: caller ต้องรู้เองว่า nullable column combination ไหนหมายถึง evidence ชนิดใด

### Failure mode

`ReportDataService` group non-support evidence ด้วย `evaluation_list_id` ทำให้ workload evidence ที่มี `evaluation_list_id` อาจถูกแสดงเป็น evidence ทั่วไปของ quantity/quality item ใน list เดียวกัน

### Coverage gap

มี test ว่า evidence ไม่ถูกลบข้ามชนิด แต่ยังไม่มี test ว่า evidence แสดงแยกชนิดถูกต้อง

### แนวทางแก้

Deepen `EvidenceAnswer` typing Module:

- `saveQualityEvidence`
- `saveSupportEvidence`
- `saveWorkloadEvidence`
- `listEvidenceByPurpose`

หรือเพิ่ม explicit `purpose/type` column เพื่อให้ Interface ไม่ขึ้นกับ nullable column inference

## B-05 — Support evidence replacement อาจลบ link เดิม

Severity: `P1`

### จุดที่พบ

- `app/Services/SupportScoreService.php`
- method: `persist`

### การทำงานปัจจุบัน

เมื่อ criterion อยู่ใน payload ระบบลบ evidence links เดิมของ criterion นั้น แล้วสร้างใหม่จาก payload

### สาเหตุของ defect

เหมือน score persistence: Interface ไม่ระบุว่า evidence payload เป็น full replace หรือ patch

### Failure mode

ถ้า client ส่ง reduced evidence set หรือ field หาย link เดิมจะถูกลบแบบเงียบ

### Coverage gap

มี test เรื่อง required evidence และ dedupe แต่ยังไม่มี partial evidence retention test

### แนวทางแก้

ให้ `ScorePersistence` กำหนด evidence operation mode และ require explicit delete action หากต้องการลบ link

## B-06 — Duplicate support criterion IDs ถูก overwrite เงียบ

Severity: `P1`

### จุดที่พบ

- `app/Services/SupportScoreService.php`
- method: `normalizeAndValidateItems`

### การทำงานปัจจุบัน

payload ที่มี `support_criteria_id` ซ้ำ จะถูก map โดย criterion id และรายการหลังอาจ overwrite รายการก่อน

### สาเหตุของ defect

Validation ตรวจว่า id มีอยู่จริง แต่ไม่ตรวจ uniqueness ภายใน payload

### Failure mode

ผู้ใช้ส่งหลาย row แต่ระบบรับเพียง row สุดท้ายของ criterion เดียวกัน โดยไม่มี error

### Coverage gap

ยังไม่มี test duplicate support criterion id

### แนวทางแก้

เพิ่ม validation rule ว่า `support_list.*.support_criteria_id` ต้อง unique ต่อ request
