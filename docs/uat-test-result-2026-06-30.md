# UAT Test Result - Workload Management Module

วันที่ทดสอบ: 2026-06-30  
เอกสารอ้างอิง: `C:\Users\pisut\Downloads\ภาคผนวก1-UAT Test Case.docx`  
Repository: `issenkku/issenkku-msu_eva-pass2`  
ผู้ทดสอบ: Codex

## สรุปผล

| สถานะ | จำนวน |
| --- | ---: |
| PASS | 35 |
| PARTIAL | 8 |
| FAIL | 0 |
| BLOCKED / NOT TESTED | 7 |
| รวม | 50 |

ผลรอบนี้ยังไม่ผ่านเกณฑ์ UAT 100% เนื่องจากยังมีรายการ `BLOCKED / NOT TESTED` 7 รายการ แต่รายการ `FAIL` จากสูตร `SUM`, `MAX`, `MIN` ได้รับการแก้ไขและทดสอบซ้ำผ่านแล้วใน `WorkloadFormulaEvaluator`

## หลักฐานการทดสอบ

### Automated test suite

Command:

```powershell
vendor\bin\pest
```

ผลลัพธ์:

- ผ่าน 222 tests
- ล้มเหลว 1 test: `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen`
- สาเหตุที่พบ: test คาด response `200 JSON` แต่ request เป็น plain form post จึงได้ `302 redirect` ตามพฤติกรรมของ `AuthController::login()`; มี test อีกชุด (`Tests\Feature\User\AuthenticateTest`) ที่ยืนยัน plain form redirect และ AJAX JSON login ผ่านแล้ว
- ข้อสรุป: เป็น automated test expectation mismatch ไม่ใช่ UAT failure โดยตรง แต่ควรแก้ test ให้สอดคล้องพฤติกรรมปัจจุบัน

### Formula probe

Command: รัน evaluator โดยตรงผ่าน PHP bootstrap

ผลลัพธ์สำคัญ:

```text
รอบที่ 1 ก่อนแก้ไข:
a+b => PASS 5
a-b => PASS 2
a*b => PASS 6
a/b => PASS 2
if(a>b,10,1) => PASS 10
sum(a,b,c) => FAIL Illuminate\Validation\ValidationException
max(a,b,c) => FAIL Illuminate\Validation\ValidationException
min(a,b,c) => FAIL Illuminate\Validation\ValidationException

รอบที่ 2 หลังแก้ไข:
sum(a,b,c) => PASS 16
max(a,b,c) => PASS 9
min(a,b,c) => PASS 2
```

### Route verification

ยืนยัน route สำหรับ workload/evaluatee/audit/dashboard แล้ว:

- Workload routes: 23 routes เช่น `workload-forms`, `workload-form-fields`, `workload-entries`, `workload-config/save`, `evaluatee/workload-entries`
- Evaluation routes: 4 routes เช่น `evaluation-workload`, `evaluation/{id}/scores`
- Dashboard routes: 9 routes สำหรับ admin/manager/director/evaluator/evaluatee
- Audit log routes: 2 routes คือ `user.management.log` และ `user.management.log.show`

## รายละเอียดผลตาม UAT

### หมวดที่ 1 การบริหารจัดการรูปแบบข้อมูลภาระงาน (Admin)

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-ADM-001 | สร้างแบบฟอร์มภาระงานใหม่ | PASS | 1 | มี API `workload-forms.store` และ `workload-config.save`; automated tests ยืนยันการ save workload config |
| UAT-ADM-002 | เพิ่ม Field ประเภทข้อความ | PASS | 1 | `workload_form_fields.field_type` รองรับ `text`; validation กันไม่ให้ใช้ text variable ในสูตร |
| UAT-ADM-003 | เพิ่ม Field ประเภทตัวเลข | PASS | 1 | `StoreWorkloadEntryRequest` บังคับ field number/item ต้องเป็นตัวเลขและไม่ติดลบ |
| UAT-ADM-004 | แก้ไขแบบฟอร์ม | PASS | 1 | `workload-forms.update`, `workload-form-fields.update`, `workload-config.save` พร้อม automated coverage |
| UAT-ADM-005 | ลบ Field | PASS | 1 | `workload-form-fields.destroy` และ `workload-config.save` ลบ field เดิมก่อนสร้างใหม่ |
| UAT-ADM-006 | กำหนดสูตรคำนวณคะแนน | PASS | 1 | บันทึก `formula_logic` ได้ และ formula probe ยืนยันสูตรพื้นฐาน |
| UAT-ADM-007 | ตรวจสอบสูตรผิดพลาด | PASS | 1 | `validateFormulaLogic()` ตรวจตัวแปรไม่รู้จัก, text variable, วงเล็บผิดพลาด |
| UAT-ADM-008 | เปิดใช้งานแบบฟอร์ม | PASS | 1 | Workflow จริงไม่มี publish endpoint/status แยก; แบบฟอร์มจะแสดงเมื่อ admin assign งานให้ผู้ถูกประเมิน แล้ว report ผูกกับ criteria version ที่มี workload form (`evaluation-workload` โหลดจาก `report_id` และ `quantity_sub_criteria_id`) |

### หมวดที่ 2 การบันทึกข้อมูลภาระงาน (Evaluatee)

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-EVA-001 | เปิดหน้าบันทึกภาระงาน | PASS | 1 | มี route `evaluation-workload` ภายใต้ role ผู้รับการประเมิน |
| UAT-EVA-002 | เพิ่มข้อมูลภาระงาน | PASS | 1 | `UAT-LOG-001` ทดสอบ POST `evaluatee.workload-entries.store` ผ่าน |
| UAT-EVA-003 | กรอกข้อมูลไม่ครบ | PASS | 1 | request validation ตรวจ required report/form/field values |
| UAT-EVA-004 | แก้ไขข้อมูลภาระงาน | PASS | 1 | `UAT-LOG-002` ทดสอบ PUT `evaluatee.workload-entries.update` ผ่าน |
| UAT-EVA-005 | ลบข้อมูลภาระงาน | PASS | 1 | `UAT-LOG-003` ทดสอบ DELETE `evaluatee.workload-entries.destroy` ผ่าน |
| UAT-EVA-006 | แสดงรายการที่บันทึก | PARTIAL | 1 | route/view มีอยู่ แต่ยังไม่ได้ทำ browser/manual assertion ครบตามหน้าจอสรุป |
| UAT-EVA-007 | แนบลิงก์ Google Drive | PARTIAL | 1 | controller รับ `evidence_links` และบันทึกเป็น `EvidenceAnswer`; ยังไม่มี test เฉพาะ Google Drive URL |
| UAT-EVA-008 | แนบลิงก์ OneDrive | PARTIAL | 1 | controller รับ URL string ได้ทั่วไป; ยังไม่มี test เฉพาะ OneDrive URL |
| UAT-EVA-009 | แนบหลายหลักฐาน | PARTIAL | 1 | controller loop บันทึกหลาย `evidence_links`; ยังไม่มี automated assertion เฉพาะหลาย URL |
| UAT-EVA-010 | ดูคะแนนรวม | PARTIAL | 1 | มี route/view summary และ score read path แต่ยังไม่ได้ assert read-only จาก browser |

### หมวดที่ 3 การคำนวณคะแนน

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-SCR-001 | คำนวณจากสูตรพื้นฐาน | PASS | 1 | formula probe ผ่าน |
| UAT-SCR-002 | คำนวณสูตรบวก | PASS | 1 | `a+b => 5` |
| UAT-SCR-003 | คำนวณสูตรลบ | PASS | 1 | `a-b => 2` |
| UAT-SCR-004 | คำนวณสูตรคูณ | PASS | 1 | `a*b => 6` |
| UAT-SCR-005 | คำนวณสูตรหาร | PASS | 1 | `a/b => 2` |
| UAT-SCR-006 | คำนวณเงื่อนไข IF | PASS | 1 | `if(a>b,10,1) => 10` |
| UAT-SCR-007 | คำนวณ SUM | PASS | 2 | รอบแรก throw `ValidationException`; รอบสองหลังแก้ evaluator ผ่าน `sum(a,b,c) => 16` |
| UAT-SCR-008 | คำนวณ MAX | PASS | 2 | รอบแรก throw `ValidationException`; รอบสองหลังแก้ evaluator ผ่าน `max(a,b,c) => 9` |
| UAT-SCR-009 | คำนวณ MIN | PASS | 2 | รอบแรก throw `ValidationException`; รอบสองหลังแก้ evaluator ผ่าน `min(a,b,c) => 2` |
| UAT-SCR-010 | แก้ไขข้อมูลแล้วคำนวณใหม่ | PARTIAL | 1 | update path เรียก evaluator ใหม่ แต่ยังไม่มี assertion เฉพาะค่าคะแนนเปลี่ยนหลังแก้ |

### หมวดที่ 4 การเชื่อมต่อระบบประเมินผลหลัก

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-INT-001 | ส่งคะแนนเข้าสู่ระบบหลัก | BLOCKED / NOT TESTED | 0 | ไม่พบ endpoint/credential/contract ของระบบหลักภายนอกในรอบนี้ |
| UAT-INT-002 | ตรวจสอบความถูกต้องของคะแนน | BLOCKED / NOT TESTED | 0 | ต้องมีระบบปลายทางหรือ expected integration contract เพื่อเทียบคะแนน |
| UAT-INT-003 | แก้ไขคะแนนแล้ว Sync ใหม่ | BLOCKED / NOT TESTED | 0 | ไม่พบ sync job/API ภายนอกที่ยืนยันได้ |
| UAT-INT-004 | กรณีระบบปลายทางไม่พร้อม | BLOCKED / NOT TESTED | 0 | ไม่มี mock/endpoint สำหรับจำลองปลายทางล้มเหลว |
| UAT-INT-005 | ตรวจสอบข้อมูลซ้ำ | BLOCKED / NOT TESTED | 0 | ต้องมี integration storage/unique contract ของระบบหลัก |

### หมวดที่ 5 สิทธิ์การใช้งาน (Role & Permission)

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-ROL-001 | สิทธิ์ Admin | PASS | 1 | route admin และ tests admin access ผ่าน |
| UAT-ROL-002 | สิทธิ์ Manager ผู้บริหาร | PASS | 1 | `Tests\Feature\Evaluation\ManagerTest` ผ่าน |
| UAT-ROL-003 | สิทธิ์ Director กรรมการ | PASS | 1 | `Tests\Feature\Evaluation\DirectorTest` ผ่าน |
| UAT-ROL-004 | สิทธิ์ Evaluator ผู้ประเมิน | PASS | 1 | `Tests\Feature\Evaluation\EvaluatorTest` ผ่าน |
| UAT-ROL-005 | สิทธิ์ Evaluatee ผู้รับการประเมิน | PASS | 1 | `Tests\Feature\Evaluation\EvaluateeTest` ผ่าน |
| UAT-ROL-006 | การเข้าถึงข้อมูลผู้อื่น | PASS | 1 | evaluation role tests ครอบคลุม cannot access/edit phase และ wrong-role access หลายกรณี |
| UAT-ROL-007 | การแก้ไขข้อมูลข้ามสิทธิ์ | PASS | 1 | role-specific edit denial tests ผ่าน |

### หมวดที่ 6 Audit Log

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-LOG-001 | บันทึกการเพิ่มข้อมูล | PASS | 1 | `UAT-LOG-001 records a log when adding workload data` ผ่าน |
| UAT-LOG-002 | บันทึกการแก้ไขข้อมูล | PASS | 1 | `UAT-LOG-002 records a log when editing workload data` ผ่าน |
| UAT-LOG-003 | บันทึกการลบข้อมูล | PASS | 1 | `UAT-LOG-003 records a log when deleting workload data` ผ่าน |
| UAT-LOG-004 | แสดงผู้ดำเนินการ | PASS | 1 | audit log page แสดง actor name/email ผ่าน |
| UAT-LOG-005 | แสดงวันเวลา | PASS | 1 | audit log timestamp แสดงวันที่/เวลาไทยผ่าน |

### หมวดที่ 7 ประสิทธิภาพและความปลอดภัย

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-PER-001 | ความเร็วการเปิดหน้าจอ | PARTIAL | 1 | มี query budget tests สำหรับ admin dashboard และ workload config; ยังไม่ได้วัด wall-clock ไม่เกิน 3 วินาทีด้วย browser/local server |
| UAT-PER-002 | ความเร็วการบันทึกข้อมูล | PARTIAL | 1 | มี query budget test สำหรับ `workload-config.save`; ยังไม่ได้วัด save workflow ไม่เกิน 3 วินาที |
| UAT-PER-003 | การใช้งานพร้อมกัน 50 คน | BLOCKED / NOT TESTED | 0 | ยังไม่ได้รัน load/concurrency test 50 users |
| UAT-SEC-001 | การเข้ารหัส HTTPS | BLOCKED / NOT TESTED | 0 | ยังไม่ได้รัน HTTPS local หรือ reverse proxy เพื่อยืนยัน URL เป็น HTTPS |
| UAT-SEC-002 | Session Login | PASS | 1 | logout tests ผ่าน และ guest redirect/login access tests ผ่าน |

## ประเด็นต้องแก้ก่อน UAT ผ่าน

1. จัดเตรียมระบบปลายทาง/contract/mock สำหรับ UAT-INT-001 ถึง UAT-INT-005
2. รัน browser/manual UAT เพิ่มสำหรับหน้าจอสรุป, evidence URL หลายรายการ, read-only score, performance 3 วินาที, concurrent 50 users, และ HTTPS local
3. แก้ automated auth test ที่คาด `200 JSON` ทั้งที่ request เป็น plain form post เพื่อให้ test suite เขียวทั้งหมด
