# ตารางเทียบ TOR กับสถานะระบบสำหรับการประชุม Kickoff

เอกสารนี้เป็นโพยภายในสำหรับ Team Lead ใช้เลือกสิ่งที่จะพูดและสาธิตในวันที่ 15 กรกฎาคม 2569 สถานะในตารางอ้างอิงจากโค้ดและชุดทดสอบที่มีอยู่ใน repository ไม่ใช่เอกสารรับรองการส่งมอบ

## ความหมายของสถานะ

- **พร้อมสาธิต** — มีหน้าจอหรือเส้นทางใช้งานที่สามารถเปิดให้เห็นได้ และมีหลักฐานในโค้ดหรือชุดทดสอบ
- **ยืนยันด้วยหลักฐาน** — มีโค้ดหรือชุดทดสอบรองรับ แต่ไม่ควรใช้เวลาเปิดหน้าจอระหว่างเดโมหลัก
- **อยู่ในแผน/รอข้อสรุป** — ยังต้องยืนยันกับเจ้าของระบบ ผู้ใช้ หรือคณะกรรมการก่อนกล่าวว่าเสร็จสมบูรณ์

## ตารางเทียบข้อกำหนด

| หัวข้อ TOR | สถานะ | สิ่งที่แสดงหรืออธิบาย | หลักฐานใน repo | คำพูดที่ปลอดภัย |
|---|---|---|---|---|
| Admin กำหนดโครงสร้างแบบฟอร์มภาระงาน | พร้อมสาธิต | หน้า Workload Config การจัดกลุ่ม รายการ และ Field | `routes/report.php`; `resources/views/workload/app.blade.php`; `tests/Feature/WorkloadConfigControllerTest.php` | ระบบปัจจุบันรองรับการกำหนดโครงสร้างภาระงานและ Field แล้วครับ |
| รองรับประเภทข้อมูลและตัวแปรสำหรับคำนวณ | พร้อมสาธิต | Field ชนิดตัวเลข ข้อความ และตัวแปรในสูตร | `resources/views/workload/partials/app-script.blade.php`; `app/Models/WorkloadFormField.php`; `tests/Feature/WorkloadConfigControllerTest.php` | Admin สามารถกำหนด Field และตัวแปรที่ใช้คำนวณให้เหมาะกับภาระงานแต่ละชนิดได้ครับ |
| สูตรคำนวณทางคณิตศาสตร์ | พร้อมสาธิต | ตัวดำเนินการพื้นฐานและฟังก์ชัน SUM, MAX, MIN | `app/Support/WorkloadFormulaEvaluator.php`; `tests/Feature/WorkloadFormulaEvaluatorTest.php`; `tests/Feature/WorkloadConfigControllerTest.php` | ระบบรองรับสูตรพื้นฐานและฟังก์ชันรวม ค่าสูงสุด และค่าต่ำสุด โดยสูตรจริงต้องได้รับการยืนยันจากหน่วยงานครับ |
| พรีวิวสูตรที่อ่านง่าย | พร้อมสาธิต | ป้อนสูตรแล้วแสดงชื่อข้อมูลและเครื่องหมายคำนวณในภาษาที่อ่านง่าย | `resources/js/workload-formula-preview.js`; `tests/js/workload-formula-preview.test.mjs` | หน้าตั้งค่ามีพรีวิวเพื่อช่วยให้ผู้กำหนดสูตรตรวจความหมายก่อนบันทึกครับ |
| Evaluatee เพิ่ม แก้ไข และลบข้อมูลภาระงาน | พร้อมสาธิต | หน้าประเมินตนเองและรายการภาระงาน | `routes/web.php`; `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`; `tests/Feature/UatAuditLogTest.php` | ผู้รับการประเมินสามารถบันทึก ปรับปรุง และลบรายการภาระงานของตนได้ครับ |
| คำนวณคะแนนจากข้อมูลที่บันทึก | พร้อมสาธิต | กรอกข้อมูลตัวอย่างแล้วแสดงคะแนนที่คำนวณได้ | `app/Support/WorkloadFormulaEvaluator.php`; `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`; `tests/Feature/WorkloadFormulaEvaluatorTest.php` | เมื่อบันทึกข้อมูล ระบบจะประเมินสูตรและจัดเก็บคะแนนที่คำนวณได้ครับ |
| คำนวณใหม่เมื่อข้อมูลเปลี่ยน | ยืนยันด้วยหลักฐาน | อธิบายผลจากการแก้ไขรายการและการเปลี่ยนคะแนน | `routes/web.php`; `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`; `tests/Feature/UatAuditLogTest.php` | การแก้ไขข้อมูลภาระงานผ่านเส้นทางเดียวกันจะปรับข้อมูลและบันทึกประวัติการเปลี่ยนแปลงครับ |
| แนบลิงก์หลักฐานจากระบบจัดเก็บเอกสาร | ยืนยันด้วยหลักฐาน | การตรวจ URL และข้อมูล evidence links | `resources/views/partials/evaluatee-evaluation-script.blade.php`; `app/Http/Requests/Workload/StoreWorkloadEntryRequest.php`; `app/Support/EvaluateeWorkloadViewData.php` | ระบบรองรับลิงก์หลักฐานและตรวจรูปแบบ URL ส่วนสิทธิ์เปิดเอกสารขึ้นกับการตั้งค่าของระบบจัดเก็บต้นทางครับ |
| สิทธิ์ Admin, Manager, Director, Evaluator และ Evaluatee | ยืนยันด้วยหลักฐาน | แสดงเมนูตามบทบาทและยืนยันด้วย Route middleware | `routes/web.php`; `resources/views/layouts/app.blade.php`; `tests/Feature/Evaluation/ManagerTest.php`; `tests/Feature/Evaluation/DirectorTest.php`; `tests/Feature/Evaluation/EvaluatorTest.php`; `tests/Feature/Evaluation/EvaluateeTest.php` | ระบบแยกเส้นทางและหน้าที่ตามบทบาทหลักแล้ว โดยจะยืนยันสิทธิ์รายเมนูกับหน่วยงานอีกครั้งครับ |
| Audit Log การเพิ่ม แก้ไข และลบภาระงาน | ยืนยันด้วยหลักฐาน | ผลทดสอบผู้ดำเนินการ เหตุการณ์ และเวลา | `tests/Feature/UatAuditLogTest.php`; `tests/Feature/WorkloadConfigControllerTest.php`; `resources/views/user/management/partials/log-table-section.blade.php` | ระบบบันทึกการเปลี่ยนแปลงที่สำคัญเพื่อให้ตรวจสอบผู้ดำเนินการและเวลาได้ครับ |
| ประสิทธิภาพการโหลดและบันทึกการตั้งค่าภาระงาน | ยืนยันด้วยหลักฐาน | อธิบายว่ามีการควบคุมจำนวน Query ในชุดทดสอบ | `tests/Feature/WorkloadConfigControllerTest.php` | มีชุดทดสอบควบคุมจำนวน Query ของหน้าตั้งค่าภาระงาน แต่การทดสอบผู้ใช้พร้อมกันตามเกณฑ์ UAT ต้องทำในสภาพแวดล้อมทดสอบร่วมกันครับ |
| HTTPS และ Session Login | ยืนยันด้วยหลักฐาน | คู่มือ HTTPS สำหรับ UAT และชุดทดสอบ authentication | `docs/local-https-uat-guide-th.md`; `tests/Feature/Auth/AuthenticationTest.php`; `tests/Feature/LoginFormTest.php` | ระบบมีแนวทางใช้งาน UAT ผ่าน HTTPS และมีการทดสอบการเข้าสู่ระบบ ส่วนโดเมนจริงต้องยืนยันในขั้นติดตั้งครับ |
| ส่งคะแนนเข้าสู่ระบบประเมินหลัก Phase 1 | อยู่ในแผน/รอข้อสรุป | ไม่สาธิตเป็นการเชื่อมต่อจริง | TOR ข้อ 4.4.2 และข้อ 5; `docs/superpowers/specs/2026-07-14-kickoff-system-preview-design.md` | ส่วนนี้ยังไม่ขอระบุว่าเชื่อมต่อสมบูรณ์ จนกว่าจะยืนยันวิธีเชื่อมต่อ รหัสจับคู่ และทดสอบร่วมกับระบบ Phase 1 ครับ |
| ส่งออกรายงานภาระงานเป็น PDF หรือ Excel | อยู่ในแผน/รอข้อสรุป | อธิบายรูปแบบผลลัพธ์ที่ต้องการและขอผู้ใช้ยืนยันตัวอย่าง | TOR ข้อ 4.3.3.4; `routes/web.php`; `routes/report.php` | ระบบหลักมีความสามารถส่งออกรายงานบางส่วนอยู่แล้ว แต่รูปแบบรายงานภาระงานตาม TOR ต้องยืนยันกับผู้ใช้และทดสอบแยกครับ |
| UAT ตาม Test Case ใน TOR | อยู่ในแผน/รอข้อสรุป | แสดงหัวข้อ Test Case และขอรายชื่อคณะกรรมการตรวจรับ | `docs/uat-test-result-2026-06-30.md`; `tests/Feature/UatAuditLogTest.php`; TOR ภาคผนวก 1 | repository มี automated tests รองรับหลายกรณี แต่ UAT อย่างเป็นทางการต้องดำเนินการและรับรองร่วมกับผู้ใช้ครับ |
| คู่มือ Admin, Evaluator และ Evaluatee | อยู่ในแผน/รอข้อสรุป | แจ้งรายการคู่มือที่ต้องส่งมอบและขอรูปแบบที่หน่วยงานต้องการ | `docs/user-guide-th.md`; `docs/user-guide-th.pptx`; TOR ข้อ 6.2–6.4 | มีคู่มือระบบเดิมเป็นฐาน และจะปรับให้ครอบคลุม Workflow ของ Phase 2 ก่อนส่งมอบครับ |
| การอบรมรอบที่ 1 และรอบที่ 2 | อยู่ในแผน/รอข้อสรุป | ยืนยันกลุ่มผู้เข้าอบรม รูปแบบ และวันจัดอบรม | TOR แผนโครงการและข้อเสนอการอบรม | ต้องขอรายชื่อกลุ่มเป้าหมาย วันเวลา และช่องทางอบรมจากหน่วยงานก่อนจัดตารางครับ |
| การติดตั้งและ Hypercare | อยู่ในแผน/รอข้อสรุป | สรุปว่าอยู่ในช่วงสัปดาห์ที่ 11–12 | TOR ข้อ 7.1.4 | การติดตั้งและดูแลหลังขึ้นระบบจะดำเนินการหลังปิด UAT และแก้ไขข้อบกพร่องตามลำดับครับ |

## ลำดับเดโมที่แนะนำ

1. เปิดหน้า Workload Config ด้วยบัญชี Admin
2. เลือกเกณฑ์ภาระงานและชี้ให้เห็นกลุ่ม รายการ Field และสูตร
3. แสดงพรีวิวสูตรโดยไม่แก้ไขข้อมูลจริง
4. เปลี่ยนไปยังบัญชี Evaluatee หรือแท็บที่เข้าสู่ระบบไว้แล้ว
5. เปิดรายการภาระงานตัวอย่าง ชี้ข้อมูล ลิงก์หลักฐาน และคะแนนที่คำนวณได้
6. ปิดเดโมด้วยหลักฐาน Audit Log หรือผลทดสอบ โดยไม่กล่าวว่าการเชื่อมต่อ Phase 1 เสร็จแล้ว

## เรื่องที่ต้องขอข้อสรุปในที่ประชุม

- แบบฟอร์มและสูตรชุดใดเป็นแหล่งอ้างอิงหลัก
- หลักการปัดเศษ การจัดการข้อมูลว่าง และผู้อนุมัติสูตร
- วิธีเชื่อมต่อ Phase 1 รหัสจับคู่ ระบบทดสอบ และผู้ประสานงาน
- รูปแบบรายงาน PDF/Excel ที่ต้องการ
- ผู้ทดสอบ UAT ผู้รับรองผล และนิยามข้อผิดพลาดระดับ Critical
- ผู้เข้าอบรมแต่ละรอบและวันที่สะดวก
- วันเริ่มนับแผนงาน 90 วัน
