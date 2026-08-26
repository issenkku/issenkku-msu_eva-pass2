# Epic D — Criteria Config และ Support Criteria

## Workflow ที่เกี่ยวข้อง

1. Admin สร้าง criteria version
2. Admin เพิ่ม category/evaluation list
3. Admin เปิด quantity/quality/support criteria
4. Admin edit criteria version เดิม
5. ระบบ serialize form เป็น JSON payload
6. Backend sync children ของ criteria version
7. Support criteria ถูกนำไปใช้ใน evaluation form

## Module / Interface ที่เกี่ยวข้อง

- `ReportStructureController`
- `support-criteria-template.blade.php`
- `script-create-collect-form-data.blade.php`
- `script-edit-collect-form-data.blade.php`
- `script-edit-populate-helpers.blade.php`
- `script-edit-event-listeners.blade.php`
- `SupportScoreService`
- `support-criteria-table-script.blade.php`

## D-01 — Omit support_criterias key อาจลบ support criteria ทั้ง version

Severity: `P0/P1`

### จุดที่พบ

- `app/Http/Controllers/ReportStructureController.php`
- update support sync path

### การทำงานปัจจุบัน

Backend สร้าง `$keptSupportCriteriaIds` จาก `evalListData['support_criterias'] ?? []`

ถ้า key นี้ absent หรือ array ว่าง ระบบอาจเข้า delete query ที่ลบ support criteria ที่ไม่อยู่ใน kept list

### สาเหตุของ defect

Interface ของ payload ไม่แยกความหมาย:

- support block ไม่ถูกแก้
- support block ถูกปิด
- support block ตั้งใจลบทั้งหมด

Implementation จึงตีความ missing key เป็น “ไม่มี support criteria ที่ต้องเก็บ”

### Failure mode

เมื่อ edit criteria version แล้ว support block hidden/unchecked หรือ frontend ไม่ส่ง key อาจลบ support criteria เดิมทั้งหมด

### Coverage gap

มี template tests แต่ยังไม่มี end-to-end edit-flow test ว่า omit key ไม่ลบ row ที่ไม่เกี่ยวข้อง

### แนวทางแก้

กำหนด payload contract ใหม่:

- missing key = preserve
- empty array + explicit `support_enabled=false` = remove
- explicit `delete_support_criterias=true` = destructive action

หรือแยก support criteria sync เป็น Module/Interface เฉพาะ

## D-02 — Create/Edit form handling ไม่สอดคล้องกัน

Severity: `P2`

### จุดที่พบ

- `script-create-collect-form-data.blade.php`
- `script-edit-collect-form-data.blade.php`

### การทำงานปัจจุบัน

create path บางจุด silently return/skip block ที่ invalid ส่วน edit path throw hard error

### สาเหตุของ defect

create/edit มี Implementation validation ของตัวเอง ไม่ใช้ shared form serializer/validator

### Failure mode

ผู้ใช้เจอพฤติกรรมต่างกัน:

- create: กรอกบาง block แล้วระบบข้ามเงียบ ๆ
- edit: ระบบ block ด้วย error

ทำให้ข้อมูลหายหรือผู้ใช้ไม่เข้าใจว่าต้องแก้ field ไหน

### Coverage gap

ยังไม่มี contract test ว่า create/edit serialize invalid form เหมือนกัน

### แนวทางแก้

สร้าง `CriteriaFormSerializer` frontend Module:

- shared validation
- shared error format
- no silent skip
- create/edit ใช้ function เดียวกัน

## D-03 — Support move buttons ใน edit mode ไม่ wired

Severity: `P2`

### จุดที่พบ

- `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php`

### การทำงานปัจจุบัน

UI มีปุ่ม move up/down สำหรับ support criteria แต่ edit script ไม่มี handler เทียบเท่า create path

### สาเหตุของ defect

UI affordance กับ event listener ไม่ได้ถูก test เป็น contract เดียวกัน

### Failure mode

ผู้ใช้คลิก reorder แล้วไม่มีอะไรเกิดขึ้น หรือ reorder ใช้ได้เฉพาะ drag ซึ่งอาจไม่ accessible

### Coverage gap

ไม่มี test สำหรับ click-reorder controls ใน edit mode

### แนวทางแก้

เพิ่ม shared reorder Module สำหรับ quantity/quality/support และใช้ทั้ง create/edit

## D-04 — Backend/frontend support total cap ไม่ตรงกัน

Severity: `P1`

### จุดที่พบ

- `app/Services/SupportScoreService.php`
- `resources/views/components/support-criteria-table-script.blade.php`

### การทำงานปัจจุบัน

Backend sum weighted score แบบ uncapped ส่วน frontend display ใช้ `Math.min(total, 100)`

### สาเหตุของ defect

นิยาม score total อยู่สองที่ โดยไม่มี `ReportScoreReadModel` เป็น source of truth

### Failure mode

ผู้ใช้เห็น UI เป็น 100 แต่ persisted score เกิน 100

### Coverage gap

มี service test ว่า uncapped ได้ แต่ไม่มี UI/backend consistency test

### แนวทางแก้

กำหนด policy ชัด:

- persisted raw score
- displayed capped score
- grand total ใช้ตัวไหน

แล้ว expose ผ่าน read model เดียว

## D-05 — Sequence collision ของ support criteria

Severity: `P2`

### จุดที่พบ

- `database/migrations/2026_07_20_000001_create_support_criterias_table.php`
- `app/Models/EvaluationList.php`

### การทำงานปัจจุบัน

มี index บน sequence แต่ไม่ unique ต่อ evaluation list

### สาเหตุของ defect

Persistence layer ไม่ enforce invariant ว่า support criteria ใน evaluation list เดียวกันต้องมี sequence ไม่ซ้ำ

### Failure mode

ลำดับ row ไม่ deterministic โดยเฉพาะหลัง reorder หรือ duplicate DOM/payload

### Coverage gap

ไม่มี DB-level uniqueness test

### แนวทางแก้

เพิ่ม unique constraint หรือ normalize sequence ก่อน persist:

- `(evaluation_list_id, sequence)` unique
- หรือ reorder transaction ที่เขียน sequence ใหม่ทั้งหมดเสมอ

## D-06 — No-horizontal-scroll ขัดกับ rich text ยาว

Severity: `P2`

### จุดที่พบ

- `resources/views/components/support-criteria-table.blade.php`
- `tests/Feature/SupportCriteriaEvaluationViewTest.php`

### การทำงานปัจจุบัน

table fixed layout และไม่มี horizontal scroll container ตามข้อกำหนด no-horizontal-scroll

### สาเหตุของ defect

Constraint ด้าน layout ถูก test แบบ static แต่ยังไม่ได้ verify ด้วย content ยาวและ viewport แคบ

### Failure mode

rich text ยาวอาจ clip/truncate หรือทำให้ input/action เข้าไม่ถึงบนหน้าจอเล็ก

### Coverage gap

ไม่มี browser responsive test สำหรับ long rich text

### แนวทางแก้

ใช้ responsive layout ที่เปลี่ยน table เป็น stacked/cards บน viewport แคบ แทนการบังคับ table fixed อย่างเดียว
