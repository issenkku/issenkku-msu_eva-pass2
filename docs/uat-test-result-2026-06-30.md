# UAT Test Result - Workload Management Module

วันที่ทดสอบ: 2026-06-30  
เอกสารอ้างอิง: `C:\Users\pisut\Downloads\ภาคผนวก1-UAT Test Case.docx`  
Repository: `issenkku/issenkku-msu_eva-pass2`  
ผู้ทดสอบ: Codex

## สรุปผล

| สถานะ | จำนวน |
| --- | ---: |
| PASS | 31 |
| PARTIAL | 8 |
| FAIL | 3 |
| BLOCKED / NOT TESTED | 8 |
| รวม | 50 |

ผลรอบนี้ยังไม่ผ่านเกณฑ์ UAT 100% เนื่องจากมีรายการ `FAIL` 3 รายการ และ `BLOCKED / NOT TESTED` 8 รายการ โดยรายการที่ล้มเหลวชัดเจนคือสูตร `SUM`, `MAX`, `MIN` ยังไม่รองรับใน `WorkloadFormulaEvaluator`

## หลักฐานการทดสอบ

### Automated test suite

Command:

```powershell
composer test
```

ผลลัพธ์:

- ผ่าน 219 tests
- ล้มเหลว 1 test: `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen`
- สาเหตุที่พบ: test คาด response `200 JSON` แต่ request เป็น plain form post จึงได้ `302 redirect` ตามพฤติกรรมของ `AuthController::login()`; มี test อีกชุด (`Tests\Feature\User\AuthenticateTest`) ที่ยืนยัน plain form redirect และ AJAX JSON login ผ่านแล้ว
- ข้อสรุป: เป็น automated test expectation mismatch ไม่ใช่ UAT failure โดยตรง แต่ควรแก้ test ให้สอดคล้องพฤติกรรมปัจจุบัน

### Formula probe

Command: รัน evaluator โดยตรงผ่าน PHP bootstrap

ผลลัพธ์สำคัญ:

```text
a+b => PASS 5
a-b => PASS 2
a*b => PASS 6
a/b => PASS 2
if(a>b,10,1) => PASS 10
sum(a,b,c) => FAIL Illuminate\Validation\ValidationException
max(a,b,c) => FAIL Illuminate\Validation\ValidationException
min(a,b,c) => FAIL Illuminate\Validation\ValidationException
```

### Route verification

ยืนยัน route สำหรับ workload/evaluatee/audit/dashboard แล้ว:

- Workload routes: 23 routes เช่น `workload-forms`, `workload-form-fields`, `workload-entries`, `workload-config/save`, `evaluatee/workload-entries`
- Evaluation routes: 4 routes เช่น `evaluation-workload`, `evaluation/{id}/scores`
- Dashboard routes: 9 routes สำหรับ admin/manager/director/evaluator/evaluatee
- Audit log routes: 2 routes คือ `user.management.log` และ `user.management.log.show`

## รายละเอียดผลตาม UAT

### หมวดที่ 1 การบริหารจัดการรูปแบบข้อมูลภาระงาน (Admin)

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-ADM-001 | สร้างแบบฟอร์มภาระงานใหม่ | PASS | มี API `workload-forms.store` และ `workload-config.save`; automated tests ยืนยันการ save workload config |
| UAT-ADM-002 | เพิ่ม Field ประเภทข้อความ | PASS | `workload_form_fields.field_type` รองรับ `text`; validation กันไม่ให้ใช้ text variable ในสูตร |
| UAT-ADM-003 | เพิ่ม Field ประเภทตัวเลข | PASS | `StoreWorkloadEntryRequest` บังคับ field number/item ต้องเป็นตัวเลขและไม่ติดลบ |
| UAT-ADM-004 | แก้ไขแบบฟอร์ม | PASS | `workload-forms.update`, `workload-form-fields.update`, `workload-config.save` พร้อม automated coverage |
| UAT-ADM-005 | ลบ Field | PASS | `workload-form-fields.destroy` และ `workload-config.save` ลบ field เดิมก่อนสร้างใหม่ |
| UAT-ADM-006 | กำหนดสูตรคำนวณคะแนน | PASS | บันทึก `formula_logic` ได้ และ formula probe ยืนยันสูตรพื้นฐาน |
| UAT-ADM-007 | ตรวจสอบสูตรผิดพลาด | PASS | `validateFormulaLogic()` ตรวจตัวแปรไม่รู้จัก, text variable, วงเล็บผิดพลาด |
| UAT-ADM-008 | เปิดใช้งานแบบฟอร์ม | BLOCKED / NOT TESTED | ไม่พบ publish endpoint/status โดยตรงใน route/model ที่ตรวจในรอบนี้ |

### หมวดที่ 2 การบันทึกข้อมูลภาระงาน (Evaluatee)

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-EVA-001 | เปิดหน้าบันทึกภาระงาน | PASS | มี route `evaluation-workload` ภายใต้ role ผู้รับการประเมิน |
| UAT-EVA-002 | เพิ่มข้อมูลภาระงาน | PASS | `UAT-LOG-001` ทดสอบ POST `evaluatee.workload-entries.store` ผ่าน |
| UAT-EVA-003 | กรอกข้อมูลไม่ครบ | PASS | request validation ตรวจ required report/form/field values |
| UAT-EVA-004 | แก้ไขข้อมูลภาระงาน | PASS | `UAT-LOG-002` ทดสอบ PUT `evaluatee.workload-entries.update` ผ่าน |
| UAT-EVA-005 | ลบข้อมูลภาระงาน | PASS | `UAT-LOG-003` ทดสอบ DELETE `evaluatee.workload-entries.destroy` ผ่าน |
| UAT-EVA-006 | แสดงรายการที่บันทึก | PARTIAL | route/view มีอยู่ แต่ยังไม่ได้ทำ browser/manual assertion ครบตามหน้าจอสรุป |
| UAT-EVA-007 | แนบลิงก์ Google Drive | PARTIAL | controller รับ `evidence_links` และบันทึกเป็น `EvidenceAnswer`; ยังไม่มี test เฉพาะ Google Drive URL |
| UAT-EVA-008 | แนบลิงก์ OneDrive | PARTIAL | controller รับ URL string ได้ทั่วไป; ยังไม่มี test เฉพาะ OneDrive URL |
| UAT-EVA-009 | แนบหลายหลักฐาน | PARTIAL | controller loop บันทึกหลาย `evidence_links`; ยังไม่มี automated assertion เฉพาะหลาย URL |
| UAT-EVA-010 | ดูคะแนนรวม | PARTIAL | มี route/view summary และ score read path แต่ยังไม่ได้ assert read-only จาก browser |

### หมวดที่ 3 การคำนวณคะแนน

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-SCR-001 | คำนวณจากสูตรพื้นฐาน | PASS | formula probe ผ่าน |
| UAT-SCR-002 | คำนวณสูตรบวก | PASS | `a+b => 5` |
| UAT-SCR-003 | คำนวณสูตรลบ | PASS | `a-b => 2` |
| UAT-SCR-004 | คำนวณสูตรคูณ | PASS | `a*b => 6` |
| UAT-SCR-005 | คำนวณสูตรหาร | PASS | `a/b => 2` |
| UAT-SCR-006 | คำนวณเงื่อนไข IF | PASS | `if(a>b,10,1) => 10` |
| UAT-SCR-007 | คำนวณ SUM | FAIL | `sum(a,b,c)` throw `ValidationException`; evaluator ยังไม่รองรับ function นี้ |
| UAT-SCR-008 | คำนวณ MAX | FAIL | `max(a,b,c)` throw `ValidationException`; evaluator ยังไม่รองรับ function นี้ |
| UAT-SCR-009 | คำนวณ MIN | FAIL | `min(a,b,c)` throw `ValidationException`; evaluator ยังไม่รองรับ function นี้ |
| UAT-SCR-010 | แก้ไขข้อมูลแล้วคำนวณใหม่ | PARTIAL | update path เรียก evaluator ใหม่ แต่ยังไม่มี assertion เฉพาะค่าคะแนนเปลี่ยนหลังแก้ |

### หมวดที่ 4 การเชื่อมต่อระบบประเมินผลหลัก

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-INT-001 | ส่งคะแนนเข้าสู่ระบบหลัก | BLOCKED / NOT TESTED | ไม่พบ endpoint/credential/contract ของระบบหลักภายนอกในรอบนี้ |
| UAT-INT-002 | ตรวจสอบความถูกต้องของคะแนน | BLOCKED / NOT TESTED | ต้องมีระบบปลายทางหรือ expected integration contract เพื่อเทียบคะแนน |
| UAT-INT-003 | แก้ไขคะแนนแล้ว Sync ใหม่ | BLOCKED / NOT TESTED | ไม่พบ sync job/API ภายนอกที่ยืนยันได้ |
| UAT-INT-004 | กรณีระบบปลายทางไม่พร้อม | BLOCKED / NOT TESTED | ไม่มี mock/endpoint สำหรับจำลองปลายทางล้มเหลว |
| UAT-INT-005 | ตรวจสอบข้อมูลซ้ำ | BLOCKED / NOT TESTED | ต้องมี integration storage/unique contract ของระบบหลัก |

### หมวดที่ 5 สิทธิ์การใช้งาน (Role & Permission)

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-ROL-001 | สิทธิ์ Admin | PASS | route admin และ tests admin access ผ่าน |
| UAT-ROL-002 | สิทธิ์ Manager ผู้บริหาร | PASS | `Tests\Feature\Evaluation\ManagerTest` ผ่าน |
| UAT-ROL-003 | สิทธิ์ Director กรรมการ | PASS | `Tests\Feature\Evaluation\DirectorTest` ผ่าน |
| UAT-ROL-004 | สิทธิ์ Evaluator ผู้ประเมิน | PASS | `Tests\Feature\Evaluation\EvaluatorTest` ผ่าน |
| UAT-ROL-005 | สิทธิ์ Evaluatee ผู้รับการประเมิน | PASS | `Tests\Feature\Evaluation\EvaluateeTest` ผ่าน |
| UAT-ROL-006 | การเข้าถึงข้อมูลผู้อื่น | PASS | evaluation role tests ครอบคลุม cannot access/edit phase และ wrong-role access หลายกรณี |
| UAT-ROL-007 | การแก้ไขข้อมูลข้ามสิทธิ์ | PASS | role-specific edit denial tests ผ่าน |

### หมวดที่ 6 Audit Log

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-LOG-001 | บันทึกการเพิ่มข้อมูล | PASS | `UAT-LOG-001 records a log when adding workload data` ผ่าน |
| UAT-LOG-002 | บันทึกการแก้ไขข้อมูล | PASS | `UAT-LOG-002 records a log when editing workload data` ผ่าน |
| UAT-LOG-003 | บันทึกการลบข้อมูล | PASS | `UAT-LOG-003 records a log when deleting workload data` ผ่าน |
| UAT-LOG-004 | แสดงผู้ดำเนินการ | PASS | audit log page แสดง actor name/email ผ่าน |
| UAT-LOG-005 | แสดงวันเวลา | PASS | audit log timestamp แสดงวันที่/เวลาไทยผ่าน |

### หมวดที่ 7 ประสิทธิภาพและความปลอดภัย

| รหัส | รายการทดสอบ | ผล | หมายเหตุ |
| --- | --- | --- | --- |
| UAT-PER-001 | ความเร็วการเปิดหน้าจอ | PARTIAL | มี query budget tests สำหรับ admin dashboard และ workload config; ยังไม่ได้วัด wall-clock ไม่เกิน 3 วินาทีด้วย browser/local server |
| UAT-PER-002 | ความเร็วการบันทึกข้อมูล | PARTIAL | มี query budget test สำหรับ `workload-config.save`; ยังไม่ได้วัด save workflow ไม่เกิน 3 วินาที |
| UAT-PER-003 | การใช้งานพร้อมกัน 50 คน | BLOCKED / NOT TESTED | ยังไม่ได้รัน load/concurrency test 50 users |
| UAT-SEC-001 | การเข้ารหัส HTTPS | BLOCKED / NOT TESTED | ยังไม่ได้รัน HTTPS local หรือ reverse proxy เพื่อยืนยัน URL เป็น HTTPS |
| UAT-SEC-002 | Session Login | PASS | logout tests ผ่าน และ guest redirect/login access tests ผ่าน |

## ประเด็นต้องแก้ก่อน UAT ผ่าน

1. เพิ่ม support หรือเปลี่ยน UAT สำหรับสูตร `SUM`, `MAX`, `MIN`
2. ระบุ/เปิดใช้กลไก publish form สำหรับ UAT-ADM-008 หรือปรับ test case ให้ตรงกับ workflow จริง
3. จัดเตรียมระบบปลายทาง/contract/mock สำหรับ UAT-INT-001 ถึง UAT-INT-005
4. รัน browser/manual UAT เพิ่มสำหรับหน้าจอสรุป, evidence URL หลายรายการ, read-only score, performance 3 วินาที, concurrent 50 users, และ HTTPS local
5. แก้ automated auth test ที่คาด `200 JSON` ทั้งที่ request เป็น plain form post เพื่อให้ test suite เขียวทั้งหมด
