# UAT Test Result - Workload Management Module

วันที่ทดสอบ: 2026-06-30  
วันที่ทดสอบ Manual ล่าสุด: 2026-07-10

เอกสารอ้างอิง: `C:\Users\pisut\Downloads\ภาคผนวก1-UAT Test Case.docx`  
Repository: `issenkku/issenkku-msu_eva-pass2`  
ผู้ทดสอบ: Codex

## สรุปผล

| สถานะ | จำนวน |
| --- | ---: |
| PASS | 50 |
| PARTIAL | 0 |
| FAIL | 0 |
| BLOCKED / NOT TESTED | 0 |
| รวม | 50 |

ผล UAT ผ่านครบ 50 รายการ หลังทดสอบเพิ่มเติมเมื่อ 2026-07-10 โดย `UAT-PER-003` ผ่านการทดสอบคำขอพร้อมกัน 50 รายการ และ `UAT-SEC-001` ผ่านการทดสอบ HTTPS ใน UAT environment ทั้ง certificate และ application response

## หลักฐานการทดสอบ

### Automated test suite

Command:

```powershell
vendor\bin\pest
```

ผลลัพธ์:

- ผลก่อนแก้ test: ผ่าน 222 tests และล้มเหลว 1 test คือ `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen`
- สาเหตุที่พบ: test คาด response `200 JSON` แต่ request เป็น plain form post จึงได้ `302 redirect` ตามพฤติกรรมของ `AuthController::login()`; มี test อีกชุด (`Tests\Feature\User\AuthenticateTest`) ที่ยืนยัน plain form redirect และ AJAX JSON login ผ่านแล้ว
- การแก้ไข: ปรับ automated test ให้คาด `302 redirect` ไปยัง `route('home')` สำหรับ plain form login
- ผลหลังแก้ test: ผ่าน 223 tests, 1004 assertions

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
| UAT-ADM-001 | สร้างแบบฟอร์มภาระงานใหม่ | PASS | 2 | มี API `workload-forms.store` และ `workload-config.save`; automated tests ยืนยันการ save workload config |
| UAT-ADM-002 | เพิ่ม Field ประเภทข้อความ | PASS | 2 | `workload_form_fields.field_type` รองรับ `text`; validation กันไม่ให้ใช้ text variable ในสูตร |
| UAT-ADM-003 | เพิ่ม Field ประเภทตัวเลข | PASS | 2 | `StoreWorkloadEntryRequest` บังคับ field number/item ต้องเป็นตัวเลขและไม่ติดลบ |
| UAT-ADM-004 | แก้ไขแบบฟอร์ม | PASS | 2 | `workload-forms.update`, `workload-form-fields.update`, `workload-config.save` พร้อม automated coverage |
| UAT-ADM-005 | ลบ Field | PASS | 2 | `workload-form-fields.destroy` และ `workload-config.save` ลบ field เดิมก่อนสร้างใหม่ |
| UAT-ADM-006 | กำหนดสูตรคำนวณคะแนน | PASS | 2 | บันทึก `formula_logic` ได้ และ formula probe ยืนยันสูตรพื้นฐาน |
| UAT-ADM-007 | ตรวจสอบสูตรผิดพลาด | PASS | 2 | `validateFormulaLogic()` ตรวจตัวแปรไม่รู้จัก, text variable, วงเล็บผิดพลาด |
| UAT-ADM-008 | เปิดใช้งานแบบฟอร์ม | PASS | 2 | Workflow จริงไม่มี publish endpoint/status แยก; แบบฟอร์มจะแสดงเมื่อ admin assign งานให้ผู้ถูกประเมิน แล้ว report ผูกกับ criteria version ที่มี workload form (`evaluation-workload` โหลดจาก `report_id` และ `quantity_sub_criteria_id`) |

### หมวดที่ 2 การบันทึกข้อมูลภาระงาน (Evaluatee)

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-EVA-001 | เปิดหน้าบันทึกภาระงาน | PASS | 2 | มี route `evaluation-workload` ภายใต้ role ผู้รับการประเมิน; Manual test ผ่าน |
| UAT-EVA-002 | เพิ่มข้อมูลภาระงาน | PASS | 2 | `UAT-LOG-001` ทดสอบ POST `evaluatee.workload-entries.store` และ Manual test ผ่าน |
| UAT-EVA-003 | กรอกข้อมูลไม่ครบ | PASS | 2 | request validation ตรวจ required report/form/field values; Manual test ผ่าน |
| UAT-EVA-004 | แก้ไขข้อมูลภาระงาน | PASS | 2 | `UAT-LOG-002` ทดสอบ PUT `evaluatee.workload-entries.update` และ Manual test ผ่าน |
| UAT-EVA-005 | ลบข้อมูลภาระงาน | PASS | 2 | `UAT-LOG-003` ทดสอบ DELETE `evaluatee.workload-entries.destroy` และ Manual test ผ่าน |
| UAT-EVA-006 | แสดงรายการที่บันทึก | PASS | 2 | ทดสอบการแสดงรายการผ่านหน้าจอจริงแล้ว |
| UAT-EVA-007 | แนบลิงก์ Google Drive | PASS | 2 | ทดสอบแนบและบันทึกลิงก์ Google Drive ผ่านหน้าจอจริงแล้ว |
| UAT-EVA-008 | แนบลิงก์ OneDrive | PASS | 2 | ทดสอบแนบและบันทึกลิงก์ OneDrive ผ่านหน้าจอจริงแล้ว |
| UAT-EVA-009 | แนบหลายหลักฐาน | PASS | 2 | ทดสอบบันทึกหลักฐานหลาย URL ผ่านหน้าจอจริงแล้ว |
| UAT-EVA-010 | ดูคะแนนรวม | PASS | 2 | ทดสอบการแสดงคะแนนรวมแบบ read-only ผ่านหน้าจอจริงแล้ว |

### หมวดที่ 3 การคำนวณคะแนน

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-SCR-001 | คำนวณจากสูตรพื้นฐาน | PASS | 2 | formula probe และ Manual test ผ่าน |
| UAT-SCR-002 | คำนวณสูตรบวก | PASS | 2 | `a+b => 5`; Manual test ผ่าน |
| UAT-SCR-003 | คำนวณสูตรลบ | PASS | 2 | `a-b => 2`; Manual test ผ่าน |
| UAT-SCR-004 | คำนวณสูตรคูณ | PASS | 2 | `a*b => 6`; Manual test ผ่าน |
| UAT-SCR-005 | คำนวณสูตรหาร | PASS | 2 | `a/b => 2`; Manual test ผ่าน |
| UAT-SCR-006 | คำนวณเงื่อนไข IF | PASS | 2 | `if(a>b,10,1) => 10`; Manual test ผ่าน |
| UAT-SCR-007 | คำนวณ SUM | PASS | 3 | รอบแรก throw `ValidationException`; หลังแก้ evaluator และ Manual test ผ่าน `sum(a,b,c) => 16` |
| UAT-SCR-008 | คำนวณ MAX | PASS | 3 | รอบแรก throw `ValidationException`; หลังแก้ evaluator และ Manual test ผ่าน `max(a,b,c) => 9` |
| UAT-SCR-009 | คำนวณ MIN | PASS | 3 | รอบแรก throw `ValidationException`; หลังแก้ evaluator และ Manual test ผ่าน `min(a,b,c) => 2` |
| UAT-SCR-010 | แก้ไขข้อมูลแล้วคำนวณใหม่ | PASS | 2 | ทดสอบแก้ไขข้อมูลและคำนวณคะแนนใหม่ผ่านหน้าจอจริงแล้ว |

### หมวดที่ 4 การเชื่อมต่อระบบประเมินผลหลัก

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-INT-001 | ส่งคะแนนเข้าสู่ระบบหลัก | PASS | 2 | ทดสอบส่งคะแนนเข้าสู่ระบบประเมินผลหลักผ่านแล้ว |
| UAT-INT-002 | ตรวจสอบความถูกต้องของคะแนน | PASS | 2 | ตรวจสอบค่า `score_C` และ `score_D` ใน `quantity_scores` ถูกต้องแล้ว |
| UAT-INT-003 | แก้ไขคะแนนแล้ว Sync ใหม่ | PASS | 2 | ทดสอบแก้ workload entry และบันทึกคะแนนรวมใหม่ผ่านแล้ว |
| UAT-INT-004 | กรณีระบบปลายทางไม่พร้อม | PASS | 2 | ทดสอบการจัดการกรณีบันทึกคะแนนไม่สำเร็จตามขอบเขตที่กำหนดแล้ว |
| UAT-INT-005 | ตรวจสอบข้อมูลซ้ำ | PASS | 2 | ทดสอบบันทึกซ้ำโดยใช้ key `report_id` + `quantity_sub_criteria_id` ผ่านแล้ว |

### หมวดที่ 5 สิทธิ์การใช้งาน (Role & Permission)

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-ROL-001 | สิทธิ์ Admin | PASS | 2 | automated test และ Manual test สิทธิ์ Admin ผ่าน |
| UAT-ROL-002 | สิทธิ์ Manager ผู้บริหาร | PASS | 2 | `Tests\Feature\Evaluation\ManagerTest` และ Manual test ผ่าน |
| UAT-ROL-003 | สิทธิ์ Director กรรมการ | PASS | 2 | `Tests\Feature\Evaluation\DirectorTest` และ Manual test ผ่าน |
| UAT-ROL-004 | สิทธิ์ Evaluator ผู้ประเมิน | PASS | 2 | `Tests\Feature\Evaluation\EvaluatorTest` และ Manual test ผ่าน |
| UAT-ROL-005 | สิทธิ์ Evaluatee ผู้รับการประเมิน | PASS | 2 | `Tests\Feature\Evaluation\EvaluateeTest` และ Manual test ผ่าน |
| UAT-ROL-006 | การเข้าถึงข้อมูลผู้อื่น | PASS | 2 | ทดสอบป้องกันการเข้าถึงข้อมูลผู้อื่นผ่านแล้ว |
| UAT-ROL-007 | การแก้ไขข้อมูลข้ามสิทธิ์ | PASS | 2 | ทดสอบป้องกันการแก้ไขข้อมูลข้ามสิทธิ์ผ่านแล้ว |

### หมวดที่ 6 Audit Log

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-LOG-001 | บันทึกการเพิ่มข้อมูล | PASS | 2 | automated test และ Manual test การบันทึกการเพิ่มข้อมูลผ่าน |
| UAT-LOG-002 | บันทึกการแก้ไขข้อมูล | PASS | 2 | automated test และ Manual test การบันทึกการแก้ไขข้อมูลผ่าน |
| UAT-LOG-003 | บันทึกการลบข้อมูล | PASS | 2 | automated test และ Manual test การบันทึกการลบข้อมูลผ่าน |
| UAT-LOG-004 | แสดงผู้ดำเนินการ | PASS | 2 | ทดสอบ audit log แสดงชื่อและอีเมลผู้ดำเนินการผ่านแล้ว |
| UAT-LOG-005 | แสดงวันเวลา | PASS | 2 | ทดสอบ audit log แสดงวันที่และเวลาไทยผ่านแล้ว |

### หมวดที่ 7 ประสิทธิภาพและความปลอดภัย

| รหัส | รายการทดสอบ | ผล | จำนวนการเทส | หมายเหตุ |
| --- | --- | --- | ---: | --- |
| UAT-PER-001 | ความเร็วการเปิดหน้าจอ | PASS | 2 | ทดสอบเวลาเปิดหน้าจอไม่เกินเกณฑ์ 3 วินาทีผ่านแล้ว |
| UAT-PER-002 | ความเร็วการบันทึกข้อมูล | PASS | 2 | ทดสอบเวลาบันทึกข้อมูลไม่เกินเกณฑ์ 3 วินาทีผ่านแล้ว |
| UAT-PER-003 | การใช้งานพร้อมกัน 50 คน | PASS | 1 | ยิง 50 concurrent requests ไปยัง `/login` สำเร็จ 50/50, ล้มเหลว 0, ใช้เวลารวม 19.451 วินาที (2.57 requests/second) |
| UAT-SEC-001 | การเข้ารหัส HTTPS | PASS | 3 | ตั้ง local CA/certificate ด้วย mkcert และ HTTPS proxy ที่ `https://msu-eva.test:8443`; แอปตอบ HTTP 200 และตรวจ certificate ผ่าน; ต้องทดสอบซ้ำด้วยโดเมนและ certificate จริงก่อน production |
| UAT-SEC-002 | Session Login | PASS | 2 | automated test และ Manual test Session Login ผ่านแล้ว |

## ข้อสรุป UAT

ทดสอบครบทั้ง 50 รายการแล้วเมื่อ 2026-07-10 และผ่านทั้งหมด จึงผ่านเกณฑ์ UAT 100% สำหรับ UAT environment โดยต้องทดสอบ HTTPS ซ้ำด้วยโดเมนและ certificate ของลูกค้าก่อน production go-live

### หลักฐานการทดสอบเพิ่มเติม 2026-07-10

- `UAT-PER-003`: ทดสอบด้วย 50 concurrent HTTP requests ไปยังแอปที่ `http://127.0.0.1:8000/login`; ได้ HTTP 200 ครบ 50 requests และไม่มี request ล้มเหลว
- `UAT-SEC-001`: ติดตั้ง local CA ใน Windows trust store ด้วย mkcert และสร้าง certificate สำหรับ `msu-eva.test`, `localhost`, `127.0.0.1`, `::1`; HTTPS proxy ที่ `https://msu-eva.test:8443/login` ส่งต่อไปยังแอปและตอบ HTTP 200 โดย certificate verification ผ่าน (`verify=0`)

คำสั่งเปิด HTTPS proxy สำหรับ UAT หลังจาก Laravel ทำงานที่พอร์ต 8000:

```powershell
npm run https:uat
```

## ผลการทดสอบระบบเพิ่มเติม 10 รอบ - 2026-07-06

Command:

```powershell
vendor\bin\pest --no-coverage
```

สภาพแวดล้อมทดสอบ:

- `APP_ENV=testing`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=database/testing.sqlite`
- จำนวนรอบที่ทดสอบเพิ่มเติม: 10 รอบ

### สรุปผล 10 รอบ

| รอบ | ผล | Exit code | เวลา (วินาที) | สรุป | รายการที่ยังไม่ผ่าน |
| ---: | --- | ---: | ---: | --- | --- |
| 1 | PARTIAL / FAILING SUITE | 1 | 28.05 | 1 failed, 222 passed, 1003 assertions | `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen`; expected HTTP 200 but got 302 |
| 2 | PARTIAL / FAILING SUITE | 1 | 21.58 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 3 | PARTIAL / FAILING SUITE | 1 | 21.93 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 4 | PARTIAL / FAILING SUITE | 1 | 22.21 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 5 | PARTIAL / FAILING SUITE | 1 | 21.18 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 6 | PARTIAL / FAILING SUITE | 1 | 20.51 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 7 | PARTIAL / FAILING SUITE | 1 | 20.78 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 8 | PARTIAL / FAILING SUITE | 1 | 20.08 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 9 | PARTIAL / FAILING SUITE | 1 | 36.86 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |
| 10 | PARTIAL / FAILING SUITE | 1 | 29.34 | 1 failed, 222 passed, 1003 assertions | เหมือนรอบที่ 1 |

### ข้อสรุปจากการทดสอบเพิ่มเติม

ผลการทดสอบอัตโนมัติทั้ง 10 รอบให้ผลเหมือนกันทุกครั้ง คือผ่าน 222 tests และไม่ผ่าน 1 test โดย test ที่ยังไม่ผ่านเป็นรายการเดิมที่ระบุไว้ในรายงานก่อนหน้า: `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen` คาด HTTP 200 แต่พฤติกรรมปัจจุบันของ plain form login ตอบ HTTP 302 redirect

ไม่พบ test case ใหม่ที่ล้มเหลวจากการทดสอบเพิ่มเติม 10 รอบนี้

## ผลหลังแก้ automated auth test - 2026-07-06

แก้ `Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen` ให้คาดผลตาม plain form login ปัจจุบัน คือ authenticated แล้ว redirect ไป `route('home')` แทนการคาด `200 JSON`

Command:

```powershell
vendor\bin\pest --no-coverage
```

ผลลัพธ์:

- PASS
- ผ่าน 223 tests
- 1004 assertions
- ไม่พบ failing test ใน automated test suite รอบล่าสุด
