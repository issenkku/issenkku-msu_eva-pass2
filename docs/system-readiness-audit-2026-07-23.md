# รายงานสำรวจความพร้อมของระบบก่อนนำไปใช้งาน

วันที่ตรวจ: 23 กรกฎาคม 2026  
โปรเจค: `issenkku-msu_eva-pass2`  
ขอบเขต: ตรวจแบบ read-only จาก source code, routes, controllers, services, views, migrations, tests และผลสำรวจจาก worker หลายตัว

## สรุปผลรวม

ระบบยังไม่ควรนำขึ้น production จนกว่าจะจัดการประเด็นระดับ Critical และ High ด้าน authorization, workflow state, การลบข้อมูลระหว่างบันทึก, และความสอดคล้องของหน้าจอหลักก่อน

ข้อจำกัดการตรวจ:

- ไม่ได้แก้ source code ใด ๆ
- สร้างเฉพาะไฟล์รายงานนี้
- ไม่สามารถรัน `php artisan`, route list, หรือ test suite ได้ เพราะไม่มี `vendor/autoload.php`
- ไม่สามารถรัน Vite build ได้ เพราะไม่มี `node_modules`
- ตรวจพบจาก static analysis และ worker exploration เป็นหลัก จึงควรยืนยันซ้ำหลังติดตั้ง dependency

## รายการที่ต้องแก้ก่อนใช้งานจริง

### Critical

#### 1. API รายงานและโครงสร้างรายงานเปิด public และสามารถแก้/ลบข้อมูลหลักได้

หลักฐาน:

- `routes/report.php:13` กำหนด `report-version` CRUD อยู่นอก middleware
- `routes/report.php:37` กำหนด `reports` CRUD และ score/evidence write routes อยู่นอก middleware
- `routes/web.php:206` require `routes/report.php` เข้ามาใน web routes ทั้งหมด
- `app/Http/Controllers/ReportStructureController.php:258` รับ `created_by` จาก request

ผลกระทบ:

- ผู้ไม่ login อาจอ่าน/สร้าง/แก้/ลบ criteria version, reports, scores, evidence ได้ หากรู้ endpoint
- กระทบ workflow หลักทั้งหมด: criteria configuration, report CRUD, score submission, evidence submission

ข้อเสนอ:

- ครอบ `report-version` และ `reports` ด้วย `auth:sanctum`
- แยกสิทธิ์ read/write/delete ตาม role หรือ policy
- ห้ามรับ `created_by` จาก client ให้ใช้ `$request->user()->id`
- เพิ่ม feature tests สำหรับ guest/role ที่ไม่มีสิทธิ์ ต้องได้ 401/403

#### 2. Score POST endpoints ตรวจแค่ role/status แต่ไม่ตรวจว่า user ถูก assign กับ report นั้นจริง

หลักฐาน:

- `app/Http/Controllers/Evaluatee/EvaluationScoreController.php:45` ใช้ `Reports::findOrFail($reportId)` แล้วตรวจ status
- `app/Http/Controllers/EvaluatorScoreController.php:100` ใช้ `Reports::findOrFail($reportId)` แล้วตรวจ status
- `app/Http/Controllers/Director/DirectorScoreController.php:93` ใช้ `Reports::findOrFail($reportId)` แล้วตรวจ status
- `app/Http/Controllers/Manager/ManagerScoreController.php:95` ใช้ `Reports::findOrFail($reportId)` แล้วตรวจ status
- ฝั่ง GET บางจุดตรวจ ownership แล้ว แต่ POST ไม่ได้บังคับซ้ำ

ผลกระทบ:

- user ที่มี role ถูกต้องสามารถเดา `report_id` แล้ว submit/overwrite คะแนนของคนอื่นได้
- กระทบ evaluatee submission, evaluator scoring, director approval, manager approval

ข้อเสนอ:

- ทุก write endpoint ต้อง query report ผ่าน assignment scope ของ current user
- ใช้ policy เช่น `ReportPolicy::submitAsEvaluatee`, `scoreAsEvaluator`, `scoreAsDirector`, `scoreAsManager`
- ห้ามใช้ `Reports::findOrFail($reportId)` แบบ global ใน write path

#### 3. Evaluatee workload entry CRUD สามารถแก้ข้อมูลข้าม report/user ได้

หลักฐาน:

- `app/Http/Requests/Workload/StoreWorkloadEntryRequest.php:14` และ `UpdateWorkloadEntryRequest.php:14` return `true`
- `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php:59` create entry จาก `report_id` ที่ส่งมา
- `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php:92` update ด้วย `WorkloadEntry::findOrFail($id)`
- `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php:158` delete ด้วย `WorkloadEntry::findOrFail($id)`

ผลกระทบ:

- ผู้รับการประเมินอาจสร้าง/แก้/ลบ workload evidence ของ report อื่นได้
- กระทบคะแนนภาระงานและหลักฐานประกอบโดยตรง

ข้อเสนอ:

- authorize ว่า `report_id` เป็น report ของ current evaluatee และอยู่ในสถานะที่แก้ได้
- update/delete ต้อง query entry ผ่าน report ที่ user มีสิทธิ์เท่านั้น
- เพิ่ม tests สำหรับ cross-user/cross-report write ต้อง fail

#### 4. Criteria version delete ตรวจ relationship ผิดและอาจลบข้อมูลที่ถูกใช้งานอยู่

หลักฐาน:

- `app/Http/Controllers/ReportStructureController.php:961-962` ตรวจ `reports.report_data_id = $id`
- แต่ `$id` คือ criteria version id
- migration แสดงว่า `reports.report_data_id` อ้าง `report_datas.id` และ `report_datas.criteria_version_id` อ้าง criteria version

ผลกระทบ:

- guard ก่อนลบอาจไม่พบ report ที่ใช้งาน criteria version นี้จริง
- หากลบ criteria version ที่มีการใช้งานอยู่ อาจ cascade ไปกระทบ report structure และข้อมูลประเมินที่ผูกอยู่

ข้อเสนอ:

- ตรวจผ่าน join `reports -> report_datas` แล้ว filter `report_datas.criteria_version_id = $criteriaVersion->id`
- ใช้ Eloquent relations เพื่อลดโอกาสสลับ id
- เพิ่ม tests delete criteria version ที่มี/ไม่มี report ใช้งาน

### High

#### 5. การบันทึกคะแนนแบบ partial ลบคะแนน/หลักฐานเดิมทั้งหมดของ report

หลักฐาน:

- `app/Http/Controllers/Evaluatee/EvaluationScoreController.php:108-110` ลบ quantity, quality, evidence ทั้ง report ก่อนสร้างใหม่
- `app/Http/Controllers/EvaluatorScoreController.php:126-127` ลบ quantity และ quality ทั้ง report
- `app/Http/Controllers/Director/DirectorScoreController.php:119-120` ลบ quantity และ quality ทั้ง report
- `app/Http/Controllers/Manager/ManagerScoreController.php:121-122` ลบ quantity และ quality ทั้ง report

ผลกระทบ:

- ถ้าฟอร์มส่งมาไม่ครบ, network หลุด, multi-tab, หรือ draft autosave จะทำให้ข้อมูลเดิมหาย
- evaluatee evidence มีความเสี่ยงสูงเพราะถูกลบทั้งชุด

ข้อเสนอ:

- ใช้ `updateOrCreate` ต่อ criterion/subcriterion
- ลบเฉพาะ row ที่ผู้ใช้ตั้งใจลบจริง
- แยก endpoint draft save กับ final submit
- validate full-form completeness เฉพาะตอน final submit

#### 6. แก้ assignment แล้วระบบลบ report เดิมและคะแนนที่อาจอยู่ระหว่างดำเนินการ

หลักฐาน:

- `app/Http/Controllers/AssignmentDataController.php:247` ลบ `$assignment->report`
- `app/Http/Controllers/AssignmentDataController.php:250` ลบ assignments เดิม
- `app/Http/Controllers/AssignmentDataController.php:259-264` สร้าง report/assignment ใหม่

ผลกระทบ:

- admin แก้วันที่/evaluator/evaluatee แล้ว report เดิมพร้อมคะแนน/evidence อาจหาย
- workflow ที่กำลังประเมินอยู่จะสูญเสียประวัติ

ข้อเสนอ:

- preserve report เดิมเมื่อ evaluatee/report template ยังเหมือนเดิม
- block destructive update หาก report มี score/evidence แล้ว
- ถ้าต้อง recreate ให้ทำเป็น explicit archive/recreate action พร้อม confirmation

#### 7. Export routes เปิดข้อมูลกว้างเกินไปสำหรับ non-admin roles

หลักฐาน:

- `routes/web.php:140` ให้ `admin|ผู้บริหาร|กรรมการ|ผู้ประเมิน` เข้ากลุ่ม export
- `routes/web.php:145` `/admin/export/reports` อยู่ในกลุ่มเดียวกัน
- `app/Http/Controllers/FileExportController.php:44` `adminExportDashboard()` ใช้ dataset ระดับ admin
- `app/Http/Controllers/FileExportController.php:59` `exportSingleReport($id)` export ตาม id โดยไม่มี ownership check ชัดเจน

ผลกระทบ:

- ผู้ประเมินหรือกรรมการอาจ export รายงานที่อยู่นอก scope ตนเองได้
- กระทบข้อมูลส่วนบุคคลและผลประเมิน

ข้อเสนอ:

- แยก `/admin/export/reports` ให้ admin/ผู้บริหาร ตาม policy ที่ต้องการจริง
- single report export ต้องตรวจ visibility เหมือนหน้า dashboard ของ role นั้น
- เพิ่ม tests ครอบ report id ที่ไม่ได้ assign ให้ user

#### 8. Status casing และ state machine ไม่สอดคล้องกัน

หลักฐาน:

- main workflow ใช้ `Assigned`, `Draft`, `Pending`, `Completed`, `Evaluator_draft`, `Director_assigned`, `Manager_assign`
- `app/Http/Controllers/ReportController.php:23` และ validation บางจุดใช้ uppercase เช่น `ASSIGNED`, `DRAFT`
- `app/Http/Controllers/DashboardController.php:500` ใช้ `case 'draft'`
- `app/Http/Controllers/EvaluatorController.php:601` ใช้ `case 'draft'`

ผลกระทบ:

- report ที่สร้างผ่าน API อาจใช้ status ที่ dashboard/evaluation flow ไม่รู้จัก
- UI badge อาจแสดง “ไม่ทราบสถานะ”
- เงื่อนไข editable/read-only อาจผิด phase

ข้อเสนอ:

- สร้าง source of truth เดียว เช่น enum/constant `ReportStatus`
- normalize casing และ migrate data เดิม
- เขียน tests ครบทุก transition: assigned → draft → pending → evaluator/director/manager → completed

#### 9. Admin dashboard form ถูก force read-only

หลักฐาน:

- `app/Http/Controllers/DashboardController.php:177` ตั้ง allowed statuses เป็น `[]`
- จากผล Spark ระบุว่า UI ยัง render form ที่ดูเหมือนแก้ได้ แต่ controller บังคับ readonly redirect

ผลกระทบ:

- workflow admin review/edit อาจใช้งานไม่ได้จริง
- ผู้ใช้เห็น UI ที่สื่อว่าแก้ได้ แต่ระบบบังคับอ่านอย่างเดียว

ข้อเสนอ:

- กำหนดสถานะที่ admin ควรแก้ได้ให้ชัดเจน
- ถ้า admin ไม่ควรแก้ ให้ปรับ UI เป็น read-only อย่างสอดคล้อง

#### 10. ตัวกรองปีใน dashboard/export มีความเสี่ยง พ.ศ./ค.ศ. ไม่ตรงกัน

หลักฐาน:

- `resources/views/dashboard/index.blade.php:244` แสดง year option เป็น พ.ศ. ด้วย `$y + 543`
- `app/Http/Controllers/FileExportController.php:124` และ `:178` ใช้ `whereYear(..., $year)` ตรง ๆ

ผลกระทบ:

- export/filter อาจไม่เจอข้อมูลหาก backend รับ พ.ศ. แต่ query ด้วย ค.ศ.

ข้อเสนอ:

- ตกลง contract ว่า request ส่ง ค.ศ. หรือ พ.ศ.
- ถ้าส่ง พ.ศ. ให้ convert `year - 543` ก่อน `whereYear`
- เพิ่ม test สำหรับปีไทยและปีสากล

#### 11. หน้าแก้ไข quality score มี route name ผิดและ CSS ปน markup

หลักฐาน:

- `resources/views/quality-scores/edit.blade.php:226` ใช้ `route('admin.quality-scores.update', ...)`
- routes จริงใน `routes/web.php:83-91` ใช้ชื่อ `quality-scores.update`
- worker พบ CSS ตอนต้นไฟล์มี fragment `<form>` ปนใน declaration

ผลกระทบ:

- หน้า edit quality score อาจ render error หรือ submit ไม่ได้
- CSS corruption อาจทำให้หน้าจอเพี้ยน

ข้อเสนอ:

- เปลี่ยน route เป็น `quality-scores.update`
- ลบ corrupted CSS fragment
- เพิ่ม feature/view test สำหรับเปิดหน้า edit และ submit update

### Medium

#### 12. User role assignment ไม่ validate ว่า role มีอยู่จริง

หลักฐาน:

- `app/Http/Controllers/User/UserController.php:52` และ `:595` validate role เป็น `nullable|string`
- `app/Http/Controllers/User/UserController.php:76` และ `:625` เรียก `syncRoles([$request->role])`

ผลกระทบ:

- หากส่ง role ผิด/ไม่มีจริง อาจเกิด exception หรือข้อมูล role ไม่ตรง workflow
- ถ้า role ว่างต้องตัดสินใจให้ชัดว่าจะ reject หรือ clear roles

ข้อเสนอ:

- validate ด้วย `Rule::exists('roles', 'name')`
- ระบุ behavior สำหรับ role ว่างให้ชัดเจน

#### 13. Director/manager quality scores รับค่าติดลบหรือเกินช่วงได้

หลักฐาน:

- worker พบ evaluator/evaluatee มี clamp ค่าบางจุด แต่ director/manager validate `numeric` และ save raw score
- `app/Http/Controllers/Director/DirectorScoreController.php:105`
- `app/Http/Controllers/Manager/ManagerScoreController.php:107`

ผลกระทบ:

- ค่า score ผิดช่วงอาจทำให้ dashboard average/export ผิด

ข้อเสนอ:

- ใช้ score normalization service เดียวกันทุก stage
- validate `min:0` และ max ตาม subcriteria/list

#### 14. Assignment edit page มี DOM id mismatch และ feedback ยังควรปรับให้ชัดระดับ field

หลักฐาน:

- `resources/views/assignment-data/create.blade.php:22-26` และ `edit.blade.php:26-30` มี error summary อยู่แล้ว จึงไม่ถือว่า validation errors ถูกซ่อนทั้งหมด
- `resources/views/assignment-data/edit.blade.php:213` ใช้ `selected-evaluator`
- JS ใช้ `selected-evaluators` ที่ `:398`, `:406`, `:453`

ผลกระทบ:

- หน้า edit evaluator chip/count อาจ stale หรือไม่ update
- แม้มี error summary แล้ว แต่ยังไม่มี per-field feedback ที่ทำให้ผู้ใช้แก้ field เฉพาะจุดได้เร็ว

ข้อเสนอ:

- ทำ id ให้ตรงกัน และแยก helper/component สำหรับ selected chip
- เพิ่ม `@error` ราย field เฉพาะ field สำคัญ เช่น `start_time`, `end_time`, `report_data_id`, `evaluator_id`, `evaluatees`

#### 15. Layout/build assets ไม่เป็นระบบเดียวกัน

หลักฐาน:

- มีทั้ง `vite.config.ts` และ `vite.config.js` โดย input ต่างกัน
- `resources/views/layouts/app.blade.php` โหลด Vite, CDN Tailwind, Bootstrap, jQuery, Select2, Summernote, Chart.js รวมกัน
- มีหลาย layout แยก เช่น `resources/views/layout.blade.php`, `resources/views/layouts/dashboard.blade.php`, `resources/views/layouts/user-evaluation.blade.php`, `resources/views/layouts/user-management-page.blade.php`
- `resources/views/layouts/app.blade.php:510`, `:519`, `:529` ใช้ `id="settingDropdown"` ซ้ำ

ผลกระทบ:

- dev/prod visual อาจต่างกัน
- CSS/JS conflict และ accessibility issue จาก duplicate id
- navigation/spacing/font/flash message ไม่สอดคล้องระหว่าง workflow

ข้อเสนอ:

- เหลือ Vite config เดียว
- ใช้ Tailwind ผ่าน Vite pipeline เท่านั้น ไม่โหลด CDN ซ้ำ
- consolidate authenticated shell และ guest shell
- ให้ dropdown id ไม่ซ้ำ

#### 16. Dashboard live-search JS อาจ error เพราะหา element ไม่เจอ

หลักฐาน:

- `resources/views/dashboard/index.blade.php:429` bind `document.getElementById('searchInput').addEventListener(...)`
- `resources/views/components/search-bar.blade.php:15` input ไม่มี `id="searchInput"`

ผลกระทบ:

- JS runtime error อาจทำให้ search/filter interaction หลังจากนั้นหยุดทำงาน

ข้อเสนอ:

- เพิ่ม `id="searchInput"` หรือ null guard ก่อน bind event
- เขียน browser/UI smoke test สำหรับหน้า dashboard

### Low

#### 17. Flash/success feedback ไม่สม่ำเสมอ

หลักฐาน:

- worker พบ main layout บางจุด log `session('success')` ลง console แทนแสดงผล
- บาง page มี flash block ของตัวเอง

ผลกระทบ:

- ผู้ใช้อาจไม่รู้ว่า save/delete สำเร็จหรือไม่

ข้อเสนอ:

- ทำ shared flash/toast partial สำหรับ success/error/validation
- ลด page-specific duplicate feedback

## Edge cases ที่ควรเพิ่มเป็น test ก่อน production

- Guest เรียก `POST /report-version`, `DELETE /report-version/{id}`, `POST /reports`, `PUT /reports/{id}` ต้องถูกปฏิเสธ
- User role ถูกต้องแต่ไม่ใช่เจ้าของ report submit score ต้องถูกปฏิเสธ
- Evaluatee สร้าง/update/delete workload entry ของ report คนอื่นต้องถูกปฏิเสธ
- Draft save ส่งข้อมูลบางส่วนแล้วข้อมูลเดิมต้องไม่หาย
- Assignment update เมื่อ report มี score/evidence แล้วต้อง preserve หรือ block
- Criteria version ที่มี report ใช้งานอยู่ต้องลบไม่ได้
- Export report id นอกสิทธิ์ของ evaluator/director ต้องถูกปฏิเสธ
- Filter/export ปีไทยต้องคืนข้อมูลปีที่ถูกต้อง
- Quality score edit page ต้อง render และ submit ได้
- Dashboard search ต้องไม่มี JS error เมื่อ component ไม่มี/เปลี่ยน id

## ลำดับการแก้ที่แนะนำ

1. ปิด public report APIs และแก้ criteria-version delete guard ให้ตรวจ relationship ถูกต้อง
2. เพิ่ม policy/ownership checks ทุก write/export endpoint โดย scope ตาม assignment และ current user
3. แก้ destructive save/delete paths ที่ทำให้คะแนน, evidence, report เดิมหาย
4. บังคับ server-side score validation ให้ครบทุก role และทุก stage
5. ตัดสินใจ admin dashboard workflow แล้วแก้ forced read-only ให้สอดคล้องกับ UI
6. รวม status/state machine เป็น source of truth เดียว
7. แก้ export visibility, year filter, quality score edit, assignment DOM mismatch, dashboard JS
8. จัด layout/build pipeline ให้เหลือมาตรฐานเดียว
9. ติดตั้ง dependencies แล้วรัน `composer test`, `php artisan route:list`, `npm run build`, และเพิ่ม tests ตาม edge cases ด้านบน

## ผลทดสอบเพิ่มเติมด้วย Docker + Playwright E2E

วันที่ทดสอบ: 23 กรกฎาคม 2026  
วิธีทดสอบ: รัน Docker compose dev stack, migrate/seed database, แล้วใช้ Playwright เปิดระบบผ่าน `http://localhost`

### Environment ที่ใช้ทดสอบ

- Docker engine: `28.5.1`
- Compose file: `docker-composer.yml`
- Services: `mysql`, `app`, `nginx`, `worker`
- คำสั่งหลักที่รัน:
  - `docker compose -f docker-composer.yml -p iss_e2e up -d --build`
  - `docker compose -f docker-composer.yml -p iss_e2e exec -T app php artisan migrate --seed --force`
  - `docker compose -f docker-composer.yml -p iss_e2e exec -T app php artisan route:list`

ข้อจำกัดของ E2E รอบนี้:

- Seed data มี user/roles/criteria แต่ยังไม่มี assignment round ครบทุก workflow จึงยังไม่ได้กดครบ flow ตั้งแต่สร้างรอบ → ผู้รับการประเมินส่ง → ผู้ประเมิน/กรรมการ/ผู้บริหารให้คะแนน → export
- เป็น exploratory Playwright run ไม่ได้เพิ่ม Playwright spec files เข้า repo
- มีการ copy `public/build` จาก image build ออกมาเพื่อ unblock การทดสอบ เพราะ dev compose bind-mount source host ทับไฟล์ build ที่ image สร้างไว้ ทำให้ dashboard เกิด `ViteManifestNotFoundException` ก่อน copy
- หลังทดสอบได้สั่ง `docker compose -f docker-composer.yml -p iss_e2e down -v` เพื่อลบ container/volume ของ test stack แล้ว

### ผลที่ยืนยันจาก E2E/runtime

#### E2E-1. Docker dev compose มีปัญหา build asset runtime เมื่อ bind mount source host

ผลทดสอบ:

- หลัง `docker compose up -d --build` และ migrate/seed แล้ว login page เปิดได้
- เมื่อ login เข้า dashboard ทุก role ครั้งแรกพบ `500 Internal Server Error`
- Error: `Illuminate\Foundation\ViteManifestNotFoundException`
- Path ที่ขาด: `/var/www/html/public/build/manifest.json`
- จุดที่ throw: `resources/views/layouts/app.blade.php:13`

สาเหตุที่พบ:

- Docker image build สร้าง `public/build` แล้ว
- แต่ `docker-composer.yml` bind-mount `./:/var/www/html` ทำให้ไฟล์ build ใน image ถูก source host ทับ
- host workspace ไม่มี `public/build`

ผลกระทบ:

- dev Docker stack ที่ clone ใหม่อาจ login ได้แต่เข้า dashboard แล้ว 500
- ทำให้ E2E/QA ผ่าน Docker สะดุดหากไม่มีการ build asset บน host หรือจัด volume ให้ถูก

ข้อเสนอ:

- ปรับ Docker dev workflow ให้ run `npm ci && npm run build` หรือ Vite dev server ให้ชัดเจน
- หรือแยก volume ไม่ให้ bind mount ทับ `public/build`
- เพิ่ม preflight check ว่า `public/build/manifest.json` มีอยู่ก่อนเปิด app ผ่าน Docker

#### E2E-2. Guest สามารถอ่าน public report APIs ได้จริง

ผลทดสอบแบบไม่ login:

- `GET /report-version` ตอบ `200` พร้อมข้อมูล criteria version
- `GET /reports` ตอบ `200` พร้อม JSON
- ในขณะที่ protected UI routes เช่น `/criteria-config` และ `/dashboard` redirect ไป `/login`

ผลกระทบ:

- ยืนยันว่า `report-version` และ `reports` ไม่ได้ถูก protect ด้วย auth middleware จริง

#### E2E-3. Guest สามารถสร้างและลบ report ได้จริงเมื่อมี CSRF token

ขั้นตอนทดสอบ:

1. เปิด `/login` แบบ guest เพื่อรับ CSRF token/session
2. ส่ง `POST /reports` พร้อม payload:
   - `report_data_id: 1`
   - `status: DRAFT`
3. ส่ง `DELETE /reports/1`

ผลลัพธ์:

- `POST /reports` ตอบ `201 Created` และสร้าง report ได้จริง
- `GET /reports` หลัง create เห็น report ที่สร้าง
- `DELETE /reports/1` ตอบ `200` พร้อม `{"message":"Report deleted successfully"}`
- `GET /reports` หลัง delete กลับเป็น empty list

ผลกระทบ:

- ยืนยันประเด็น Critical ว่า public API ไม่ได้แค่ read ได้ แต่ write/delete core evaluation data ได้จริง
- CSRF ไม่ใช่ authorization control เพราะ guest สามารถรับ token จากหน้า login ได้

ข้อเสนอ:

- แก้เป็นลำดับแรก: ครอบ route ด้วย `auth:sanctum`/role/policy
- เพิ่ม regression E2E/API tests ว่า guest ต้องได้ `401/403` สำหรับทุก write route

#### E2E-4. Login redirect ตาม role ทำงาน แต่ dashboard มี console/runtime errors

ทดสอบด้วย seed users:

- `001 / password` → `/dashboard` ผ่าน
- `002 / password` → `/evaluator-dashboard` ผ่าน
- `004 / password` → `/evaluatee-dashboard` ผ่าน
- `006 / password` → `/manager-dashboard` ผ่าน
- `007 / password` → `/director-dashboard` ผ่าน

ผลที่พบใน console:

- ทุก dashboard มี warning: `cdn.tailwindcss.com should not be used in production`
- ทุก dashboard มี error: `No valid scatter data found for chart myChart`
- admin dashboard มี page error: `Cannot read properties of null (reading 'addEventListener')`

ผลกระทบ:

- role redirect หลักผ่าน smoke test
- แต่ dashboard JS/UX ยังมี runtime error จริง โดยเฉพาะ admin dashboard จาก `searchInput` null ตาม finding เดิม
- chart error อาจเป็น expected empty state แต่ควร handle แบบไม่ log error ใน production

ข้อเสนอ:

- เพิ่ม null guard หรือ id ให้ `searchInput`
- ปรับ chart component ให้ handle empty dataset แบบ graceful
- เอา Tailwind CDN ออกจาก production layout

#### E2E-5. หน้าแก้ไข quality score ล้มเหลวจริง

ขั้นตอน:

1. สร้าง test report และ `quality_scores.id = 1` ใน DB จำลอง
2. Login เป็น admin
3. เปิด `/quality-scores/1/edit`

ผลลัพธ์:

- HTTP status `500`
- Error: `Symfony\Component\Routing\Exception\RouteNotFoundException`
- Message: `Route [admin.quality-scores.update] not defined.`
- จุดที่ throw: `resources/views/quality-scores/edit.blade.php:226`

ผลกระทบ:

- Admin ไม่สามารถเปิดหน้า edit quality score ที่มี record จริงได้
- ยืนยัน finding เดิมว่า route name ใน Blade ผิด

ข้อเสนอ:

- เปลี่ยนเป็น route จริง `quality-scores.update`
- เพิ่ม feature/E2E test สำหรับเปิดหน้า edit และ submit update

#### E2E-6. พบ secret exposure ใน `.env.docker`

ผลตรวจระหว่างเตรียม Docker:

- `.env.docker` มีค่า mail credential จริงอยู่ใน repo/workspace

ผลกระทบ:

- ถ้าไฟล์นี้ถูก commit/share ถือว่า credential leak

ข้อเสนอ:

- rotate credential ที่เกี่ยวข้อง
- ย้ายค่าลับไป secret manager หรือ local-only env ที่ไม่ commit
- เพิ่ม `.env.docker` เข้า policy การจัดการ secret หรือใช้ `.env.docker.example` แทน

### สรุปผล E2E รอบนี้

E2E ยืนยัน findings สำคัญหลายรายการ:

- Public report APIs เป็น Critical จริง เพราะ guest สร้างและลบ report ได้
- Docker dev workflow ยังไม่พร้อมสำหรับ QA/E2E แบบ clone แล้วรันทันที เพราะ `public/build` หายหลัง bind mount
- Role login/redirect หลักทำงาน
- Dashboard มี JS runtime errors จริง
- Quality score edit page ใช้งานไม่ได้จริง
- มีความเสี่ยง secret exposure จาก `.env.docker`

ลำดับที่ควรแก้จากผล E2E:

1. ปิด public report APIs ทันที
2. แก้ Docker dev asset workflow ให้ E2E reproducible
3. แก้ quality score edit route
4. แก้ admin dashboard `searchInput` JS error และ chart empty-state error
5. Rotate/remove secrets จาก `.env.docker`
6. เพิ่ม Playwright spec files อย่างเป็นระบบเมื่อ assignment seed/test fixtures พร้อม

## Retest หลัง update จาก branch Jui (23 กรกฎาคม 2026)

### ขอบเขตและสถานะ commit ที่ตรวจ

ตรวจซ้ำหลัง update จาก branch `Jui` ประมาณ 290+ commits โดยสถานะ repository ขณะตรวจ:

- Branch ปัจจุบัน: `dev`
- HEAD: `8f19917 test: cover subject Preview modal workflow`
- `dev`, `Jui`, `origin/dev`, `origin/Jui` ชี้ที่ commit เดียวกัน (`8f19917`)
- ทดสอบผ่าน Docker Compose project: `iss_e2e_jui`
- ใช้ฐานข้อมูลจาก seed ใหม่ใน container และทดสอบด้วย browser automation/Playwright smoke checks

> หมายเหตุ: การตรวจรอบนี้ยังเป็น readiness audit และ E2E smoke/regression test ไม่ใช่ full acceptance test ทุก workflow เพราะยังไม่มี fixture ครบสำหรับ assignment/evaluation end-to-end และ Excel import payload แบบ deterministic

### สิ่งที่เปลี่ยนจากรายงานรอบก่อน

#### Fixed-1. Public report APIs ถูกปิดจาก guest แล้ว

สถานะเดิมในรายงานก่อนหน้า:

- Guest เปิด `/report-version` และ `/reports` ได้
- Guest สามารถ `POST /reports` และ `DELETE /reports/{id}` ได้เมื่อมี CSRF token

ผลตรวจรอบใหม่:

- `routes/report.php` ครอบ route `report-version` และ `reports` ด้วย middleware `auth:sanctum` และ `role:admin`
- `GET /report-version` แบบ guest ได้ `302` ไปหน้า login
- `GET /reports` แบบ guest ได้ `302` ไปหน้า login
- `POST /reports` แบบ guest พร้อม CSRF token ได้ `401`

สรุป:

- ประเด็น Critical เดิมเรื่อง public report read/write/delete API ถือว่าแก้แล้วในรอบนี้
- ยังควรเพิ่ม regression API/E2E test เพื่อป้องกัน route หลุด middleware ในอนาคต

#### Fixed-2. การลบ criteria version ใช้ relationship ที่ถูกต้องขึ้น

สถานะเดิม:

- `ReportStructureController::destroy()` ใช้ relationship ผิด ทำให้ guard การลบ criteria version เสี่ยงไม่เห็น report ที่อ้างอิงอยู่จริง

ผลตรวจรอบใหม่:

- โค้ดเปลี่ยนมา query ผ่าน `$criteriaVersion->reportDatas()->pluck('id')`
- จากนั้นตรวจ `reports.report_data_id` ผ่าน `whereIn`

สรุป:

- ประเด็น relationship ผิดใน delete guard ถูกแก้แล้วในระดับ code path หลัก
- ยังควรมี automated test สำหรับกรณี criteria version ที่มี report แล้วห้ามลบ

#### Fixed-3. หน้า edit quality score เปิดได้แล้ว

สถานะเดิม:

- `/quality-scores/{id}/edit` ล้มเหลว `500`
- สาเหตุคือ Blade เรียก route `admin.quality-scores.update` ที่ไม่มีอยู่จริง

ผลตรวจรอบใหม่:

- Blade เปลี่ยนเป็น `route('quality-scores.update', $qualityScore->id)`
- E2E smoke: สร้าง `reports` และ `quality_scores` ทดสอบใน DB container แล้ว login เป็น admin
- เปิด `/quality-scores/1/edit` ได้ `HTTP 200`
- ไม่พบ `RouteNotFoundException`

สรุป:

- ประเด็น quality score edit route ถูกแก้แล้ว
- ยังควรเพิ่ม feature/E2E test สำหรับ submit update เพื่อยืนยัน workflow edit ครบวงจร

#### Fixed-4. Admin dashboard JS error จาก `searchInput` null ไม่เกิดซ้ำ

สถานะเดิม:

- Admin dashboard มี page error: `Cannot read properties of null (reading 'addEventListener')`

ผลตรวจรอบใหม่:

- โค้ดมีการประกาศ `const searchInput = document.getElementById('searchInput');`
- มี guard `if (searchInput) { ... }` ก่อน bind event
- E2E smoke login เข้า admin dashboard ไม่พบ page error เดิม

สรุป:

- ประเด็น runtime error จาก `searchInput` null ถูกแก้แล้ว

#### Fixed-5. Dashboard chart empty-state error ไม่พบซ้ำใน role smoke test

สถานะเดิม:

- ทุก dashboard log error: `No valid scatter data found for chart myChart`

ผลตรวจรอบใหม่:

- Login/redirect smoke test ด้วย seed users:
  - admin `001 / password` → `/dashboard`
  - evaluator `002 / password` → `/evaluator-dashboard`
  - evaluatee `004 / password` → `/evaluatee-dashboard`
  - manager `006 / password` → `/manager-dashboard`
  - director `007 / password` → `/director-dashboard`
- ทุก role redirect ผ่าน
- ไม่พบ console error `No valid scatter data found for chart myChart`

สรุป:

- ประเด็น chart empty-state error ไม่เกิดซ้ำใน smoke test รอบนี้
- ยังควรทดสอบ dataset จริง/ว่างในหน้า chart โดยตรง เพื่อยืนยันว่า empty-state ถูกจัดการครบทุกหน้าที่ใช้ chart เดียวกัน

#### New/Changed-1. Subject import/preview workflow มี feature ใหม่และ modal เปิดได้

สิ่งที่พบจาก commit ใหม่:

- มีชุด commit เพิ่ม/แก้ workflow รายวิชา เช่น subject name, subject credit, import mapping, preview modal และ accessibility test

ผล E2E smoke:

- Login เป็น admin แล้วเปิด `/subjects` ได้ `HTTP 200`
- พบปุ่ม `นำเข้าจาก Excel`
- กดปุ่มเปิด import modal ผ่าน selector `[data-subject-import-open]`
- Modal เปลี่ยนเป็นสถานะเปิดจริง (`modal fade show`, `display: block`)
- มี backdrop และ `body` เข้า state `modal-open`
- ไม่พบ page error ระหว่างเปิด modal

ข้อจำกัด:

- ยังไม่ได้ทดสอบ upload Excel และ confirm preview จริง เพราะยังไม่มี `.xlsx` fixture ที่ยืนยัน schema/expected mapping ได้แน่นอน

สรุป:

- Subject import modal smoke ผ่าน
- ควรเพิ่ม fixture Excel ขนาดเล็กสำหรับ E2E เพื่อทดสอบ import preview → confirm → persist → duplicate/invalid row handling

### ประเด็นที่ยังคงอยู่จากรายงานเดิม

#### Remain-1. Tailwind CDN ยังถูกโหลดใน layout

ผลตรวจ:

- ยังมี `https://cdn.tailwindcss.com` ใน layout/head assets
- ทุก dashboard ยังมี warning จาก browser console ว่า Tailwind CDN ไม่ควรใช้ใน production

ผลกระทบ:

- เสี่ยงด้าน performance, reproducibility และ production hardening

ข้อเสนอ:

- ใช้ Tailwind ผ่าน build pipeline (`npm run build`) และโหลด asset จาก Vite/manifest แทน CDN

#### Remain-2. Score submission ยังมี pattern ลบคะแนนเดิมก่อนสร้างใหม่

ผลตรวจ:

- ยังพบ code path ที่ลบ `QuantityScore`, `QualityScore`, `EvidenceAnswer` ตาม `report_id` ก่อนบันทึกชุดใหม่ ใน controller หลาย role

ผลกระทบ:

- ถ้า validation/transaction/partial failure เกิดกลางทาง มีความเสี่ยงข้อมูลคะแนนเดิมหายหรือ state ไม่ครบ
- ทำให้ audit trail และ recovery ยาก

ข้อเสนอ:

- ใช้ transaction ครอบทุก operation
- พิจารณา upsert/versioning แทน delete-and-recreate
- เพิ่ม test กรณี failure ระหว่างบันทึกคะแนน

#### Remain-3. Admin dashboard ยังถูก force readonly

ผลตรวจ:

- `DashboardController` ยังตั้ง `$allowedEditStatuses = [];`

ผลกระทบ:

- Admin อาจไม่สามารถ edit จาก dashboard ได้ แม้ในสถานะที่ business workflow ควรอนุญาต
- ถ้าเป็น intention ใหม่ ควรบันทึกเป็น product rule ให้ชัดเจน ไม่ควรปล่อยเป็น implicit code behavior

ข้อเสนอ:

- ยืนยัน policy กับเจ้าของระบบว่า admin ควร edit ได้ใน status ใด
- เพิ่ม test สำหรับ permission matrix ต่อ role/status

#### Remain-4. Status state machine ยังมีความเสี่ยงจาก casing/mapping ไม่สอดคล้อง

ผลตรวจ:

- ยังพบทั้ง status แบบ uppercase เช่น `ASSIGNED`, `DRAFT`
- และ status แบบ title/lowercase เช่น `Draft`, `draft`
- บางจุดใน dashboard/evaluator flow ยัง switch case ด้วย lowercase

ผลกระทบ:

- เสี่ยงเกิด branch logic ไม่ทำงานเมื่อข้อมูลมาจากคนละ source หรือคนละ controller
- เสี่ยงกับ workflow ปุ่ม action, readonly state, filtering และ report API

ข้อเสนอ:

- กำหนด enum/constant กลางสำหรับ status
- Normalize ค่าเข้า/ออกทุก controller
- เพิ่ม integration tests สำหรับ state transition ทุก role

#### Remain-5. Export filter ปี พ.ศ./ค.ศ. ยังมีความเสี่ยง

ผลตรวจ:

- UI แสดงปีเป็น พ.ศ. จาก `$y + 543`
- Backend filter ใช้ `whereYear('start_time', $year)` โดยตรง

ผลกระทบ:

- ถ้าค่าที่ส่งกลับเป็น พ.ศ. จะ query ไม่เจอข้อมูล ค.ศ.
- เสี่ยง export/report ว่างผิดพลาดตามปีที่เลือก

ข้อเสนอ:

- ยืนยัน contract ว่า request ส่งปี ค.ศ. หรือ พ.ศ.
- ถ้ารับ พ.ศ. ให้ convert เป็น ค.ศ. ก่อน query
- เพิ่ม test สำหรับปี 2569/2026

#### Remain-6. `.env.docker` ยังมี credential จริง/คล้ายจริงใน workspace

ผลตรวจ:

- `.env.docker` ยังมี secret เช่น `APP_KEY` และ mail credential

ผลกระทบ:

- ถ้าไฟล์นี้ถูก commit หรือแชร์ ถือเป็น credential exposure

ข้อเสนอ:

- Rotate credential ที่เกี่ยวข้อง
- ใช้ `.env.docker.example` สำหรับค่า template
- เก็บ secret จริงใน local secret manager/CI secret

### ประเด็นใหม่ที่พบจากรอบ Jui retest

#### New-1. Docker build พบ npm vulnerabilities

ผลตรวจระหว่าง `docker compose build`:

- `npm ci --legacy-peer-deps` รายงาน `9 vulnerabilities`
- ระดับที่รายงาน: `2 moderate`, `5 high`, `2 critical`

ผลกระทบ:

- ยังไม่สรุปว่า exploit ได้จริงใน runtime แต่ไม่ควรปล่อยผ่านก่อน production
- ต้อง triage ว่าอยู่ใน dependency ฝั่ง dev/build หรือถูก bundle ไป client/runtime

ข้อเสนอ:

- รัน `npm audit`/`npm audit fix --dry-run` ใน branch แยก
- ตรวจ breaking change ก่อน upgrade
- บันทึก risk acceptance ถ้ายังแก้ไม่ได้ทัน release

#### New-2. Docker image สำหรับ E2E/test ยังไม่พร้อมรัน test suite ตรง ๆ

ผลตรวจ:

- `php artisan test` ใน container ได้ error: `Command "test" is not defined`
- `vendor/bin/pest` ตอนแรก fail เพราะ autoload dev class `Tests\TestCase` ไม่ถูก generate
- หลัง `composer dump-autoload --dev` แล้ว Pest รันได้ แต่ fail ที่ `TestingDatabaseConfigurationTest`
- สาเหตุที่ fail: app environment ใน container เป็น `local` ไม่ใช่ `testing`
- ผล Pest หลังรันแบบ stop-on-failure: `1 failed, 19 passed, 279 pending`

ผลกระทบ:

- Docker dev/E2E environment ยังไม่ใช่ test environment ที่ reproducible
- มีความเสี่ยงที่ทีมรัน automated tests แล้วใช้ database/env ผิดชุด

ข้อเสนอ:

- เพิ่ม compose profile หรือ service สำหรับ test โดยเฉพาะ เช่น `app-test`
- ตั้ง `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=database/testing.sqlite`
- Generate autoload-dev ใน test image หรือใช้ image แยกสำหรับ CI
- เพิ่มคำสั่งมาตรฐาน เช่น `docker compose run --rm app-test ./vendor/bin/pest`

#### New-3. Clean-clone asset workflow ยังต้องยืนยันซ้ำ

สถานะเดิม:

- รอบก่อน Docker dev เคยเจอ `public/build/manifest.json` missing หลัง bind mount

ผลรอบใหม่:

- รอบนี้ไม่ reproduce อาการดังกล่าว เพราะ workspace มี ignored artifact `public/build/` ค้างอยู่จากการทดสอบก่อนหน้า
- ระบบจึงเปิดหน้าได้และ E2E smoke ไม่ติด manifest missing

ผลกระทบ:

- ยังไม่สามารถสรุปได้ว่า clean clone + docker compose จะพร้อมใช้งานทันที

ข้อเสนอ:

- ทดสอบซ้ำใน clean workspace หรือ container volume strategy ที่ไม่มี `public/build` จาก host
- กำหนดให้ dev compose build/run assets อย่างชัดเจน หรือ mount volume ที่ไม่ทับ asset ที่ build ใน image

### สรุปผล E2E รอบ Jui retest

ผ่าน:

- Docker compose build/start สำเร็จ
- Migration และ seeding สำเร็จ
- Guest ถูก redirect/deny จาก report APIs แล้ว
- Role login/redirect หลักผ่านทุก role ที่ทดสอบ
- Admin quality score edit page เปิดได้ `HTTP 200`
- Dashboard ไม่พบ runtime errors เดิมจาก `searchInput` และ chart empty dataset
- Subject import modal เปิดได้และไม่มี page error ใน smoke test

ยังไม่ผ่าน/ยังต้องแก้:

- Tailwind CDN ยังอยู่ใน production-facing layout
- Score save flow ยังมี delete-and-recreate pattern ที่เสี่ยงข้อมูลหาย
- Admin dashboard readonly policy ยังไม่ชัด
- Status casing/mapping ยังไม่เป็นระบบเดียวกัน
- Export filter ปี พ.ศ./ค.ศ. ยังเสี่ยง query ผิด
- `.env.docker` ยังมี secret จริง/คล้ายจริง
- Docker test environment ยังไม่ reproducible สำหรับ Pest/PHP test suite
- npm dependencies มี vulnerabilities ที่ต้อง triage
- Full subject import และ full evaluation workflow ยังต้องมี fixture ก่อนทดสอบครบ

### ข้อเสนอแนะด้าน UI/UX จากการทดสอบการใช้งาน

ประเด็นในกลุ่มนี้ไม่ได้เป็น bug ฝั่ง backend อย่างเดียว แต่กระทบความรู้สึกของผู้ใช้โดยตรง โดยเฉพาะระบบประเมินที่มีหลาย role, หลาย status และมี form ที่ข้อมูลสำคัญ ถ้า UI สื่อสารไม่ตรงกับ workflow ผู้ใช้จะไม่มั่นใจว่ากำลังอยู่ขั้นตอนไหน กดอะไรได้บ้าง และข้อมูลถูกบันทึกแล้วจริงหรือไม่

#### UX-1. เอา Tailwind CDN ออกจาก production-facing layout

ตอนนี้ทุก dashboard ยังมี browser warning ว่า `cdn.tailwindcss.com` ไม่ควรใช้ใน production ตรงนี้ควรจัดเป็น UI/UX readiness issue ด้วย ไม่ใช่แค่ build warning

ผลกระทบกับผู้ใช้:

- หน้าอาจโหลด style ช้าหรือไม่เสถียรถ้า CDN มีปัญหา
- visual consistency ระหว่าง local, Docker, staging และ production คุมได้ยาก
- first load และ perceived performance อาจแกว่งตาม network

ข้อเสนอ:

- ใช้ Tailwind ผ่าน Vite/build pipeline เท่านั้น
- ให้ layout โหลด compiled CSS จาก `public/build/manifest.json`
- เพิ่ม preflight/E2E check ว่า production layout ไม่มี Tailwind CDN แล้ว

#### UX-2. Admin readonly state ต้องสื่อสารให้ชัด

ระบบยังมี policy ที่ทำให้ admin dashboard เป็น read-only ผ่าน `$allowedEditStatuses = []` แต่จากมุมผู้ใช้ ถ้าหน้ายังมี form, ปุ่ม หรือ interaction ที่ดูเหมือนแก้ไขได้ จะเกิดความสับสนทันที

ผลกระทบกับผู้ใช้:

- admin เห็น UI เหมือนทำงานต่อได้ แต่ระบบไม่ให้แก้จริง
- ผู้ใช้อาจตีความว่าเป็น bug ทั้งที่อาจเป็น business rule
- support/debug ยาก เพราะปัญหาดูเหมือน permission bug หรือ workflow bug ได้ทั้งคู่

ข้อเสนอ:

- ยืนยัน product rule ว่า admin แก้ได้ใน status ใดบ้าง
- ถ้า admin ตั้งใจให้ดูอย่างเดียว ให้แสดง badge หรือ notice เช่น “โหมดอ่านอย่างเดียว”
- ปุ่มที่ทำไม่ได้ควร disable พร้อมเหตุผล ไม่ใช่ซ่อนหรือปล่อยให้กดแล้วค่อย error
- เพิ่ม permission matrix ต่อ role/status เพื่อให้ human และ automated tests อ่าน policy เดียวกัน

#### UX-3. Status mapping ต้องมี source of truth เดียว

สถานะยังปนหลายรูปแบบ เช่น `DRAFT`, `Draft`, `draft` ปัญหานี้กระทบ UX มากกว่าที่เห็นใน code เพราะ status เป็นตัวตัดสินว่าผู้ใช้เห็นปุ่มอะไร ทำ action ไหนได้ และรายการอยู่ขั้นตอนไหนของ workflow

ผลกระทบกับผู้ใช้:

- badge อาจแสดงผิดหรือแสดง “ไม่ทราบสถานะ”
- ปุ่ม submit/edit/review/approve อาจแสดงผิด role หรือผิด phase
- รายการที่ดูเหมือนสถานะเดียวกัน อาจทำงานไม่เหมือนกันเพราะค่าจริงใน database ต่าง casing

ข้อเสนอ:

- สร้าง status enum/constant กลาง
- แยกให้ชัดระหว่าง database value, display label ภาษาไทย, color token และ allowed actions
- ให้ทุก controller, Blade และ API ใช้ mapping เดียวกัน
- เพิ่ม E2E coverage สำหรับ state transition หลัก เช่น Draft → Assigned → In Review → Approved/Rejected

#### UX-4. Score save flow ต้องป้องกันความรู้สึกว่า “ข้อมูลหาย”

code path การบันทึกคะแนนยังมี pattern ลบคะแนนเดิมก่อนสร้างใหม่ ถ้าเกิด validation error, timeout หรือ exception กลางทาง ผู้ใช้มีโอกาสเจอสถานการณ์ที่ข้อมูลเดิมหายหรือไม่แน่ใจว่าระบบบันทึกอะไรไว้บ้าง

ผลกระทบกับผู้ใช้:

- ผู้ใช้กดบันทึกแล้วไม่มั่นใจว่าคะแนน/หลักฐานถูกเก็บครบหรือไม่
- ถ้าข้อมูลหายหลัง error จะกระทบ trust ของระบบสูงมาก
- ระบบประเมินควรให้ความรู้สึกว่า save ปลอดภัยและย้อนตรวจสอบได้

ข้อเสนอ:

- ครอบ score submission ด้วย transaction
- ใช้ upsert/versioning หรือ draft snapshot แทน delete-and-recreate แบบตรง ๆ
- ระหว่าง submit ให้ปุ่มอยู่ใน loading/disabled state พร้อมข้อความ เช่น “กำลังบันทึก”
- เมื่อสำเร็จให้มี success feedback ที่ชัด และเมื่อ fail ต้องบอกสาเหตุพร้อมทางไปต่อ
- ถ้าเป็น long form ควรพิจารณา autosave draft หรือ warning ก่อนออกจากหน้า

#### UX-5. Subject import ต้องทดสอบ full flow ไม่ใช่แค่ modal เปิดได้

รอบนี้ทดสอบได้ว่า subject import modal เปิดได้ ไม่มี page error และ modal state ทำงาน แต่ยังไม่ได้ทดสอบ upload Excel → preview → confirm → persist จริง

จุดที่ควรตรวจเพิ่ม:

- ไฟล์ผิด format แสดง error ที่อ่านรู้เรื่องไหม
- row ที่ invalid ระบุแถว/คอลัมน์/สาเหตุชัดไหม
- duplicate subject ถูกแยกเป็น create/update/skip อย่างไร
- preview มี summary จำนวนรายการที่จะเพิ่ม แก้ หรือข้ามหรือไม่
- ถ้าผู้ใช้ปิด modal ตอนมีข้อมูลค้าง ต้อง confirm ก่อนทิ้งข้อมูลหรือไม่

ข้อเสนอ:

- สร้าง `.xlsx` fixture ขนาดเล็กสำหรับ happy path, invalid rows และ duplicate rows
- เพิ่ม Playwright E2E สำหรับ import preview → confirm → ตรวจผลในตาราง
- ทำ import result UI ให้เป็นตารางที่ตรวจสอบได้ ไม่ใช่แค่ toast สั้น ๆ

#### UX-6. Empty state ของ chart/dashboard ต้องเป็นข้อความใช้งานได้จริง

chart error เดิมไม่เกิดซ้ำในรอบนี้ แต่ dashboard ที่ไม่มีข้อมูลยังควรมี empty state ที่ตั้งใจออกแบบ ไม่ใช่แค่พื้นที่ว่างหรือ log error เงียบ ๆ

ข้อเสนอ:

- แสดงข้อความ เช่น “ยังไม่มีข้อมูลสำหรับรอบประเมินนี้”
- ถ้ามี action ที่เกี่ยวข้อง ให้เสนอทางไปต่อ เช่น เลือกปีใหม่ สร้างรอบประเมิน หรือเริ่ม assignment
- chart/table empty state ควรใช้ pattern เดียวกันทุก dashboard
- ไม่ควรใช้ console error สำหรับ expected empty data ใน production

#### UX-7. Form feedback และ validation ควรทำให้ผู้ใช้แก้ปัญหาได้ทันที

ระบบนี้มี form สำคัญหลายจุด เช่น score, evidence, subject, report และ user management ถ้า validation feedback ไม่ชัด ผู้ใช้จะเสียเวลาลองผิดลองถูก

ข้อเสนอ:

- label ต้องเห็นชัด ไม่พึ่ง placeholder อย่างเดียว
- error message ควรอยู่ใกล้ field และบอกวิธีแก้
- ถ้ามีหลาย error ให้มี error summary ด้านบนพร้อม anchor ไป field ที่ผิด
- หลัง submit fail ควร focus ไป field แรกที่ต้องแก้
- required field ต้องสื่อสารชัด
- session timeout ต้องพาผู้ใช้กลับ login พร้อมบอกเหตุผล ไม่ใช่ปล่อยให้ form submit แล้วเงียบ

#### UX-8. Modal และ keyboard accessibility ต้องตรวจต่อก่อน production

Subject import modal เปิดได้แล้ว แต่ก่อนใช้งานจริงควรตรวจ accessibility ให้ครบ เพราะ modal เป็น interaction สำคัญและมีโอกาสใช้กับข้อมูลจำนวนมาก

ข้อเสนอ:

- กด `Esc` แล้วปิด modal ได้
- เมื่อเปิด modal แล้ว focus ต้องย้ายเข้า modal
- กด `Tab` แล้ว focus ต้องไม่หลุดไปหลัง modal
- ปุ่ม icon-only ต้องมี accessible label
- error/success ใน modal ควรประกาศผ่าน `aria-live` หรือ pattern ที่ screen reader อ่านได้
- ปุ่มปิดหรือ cancel ต้องชัด โดยเฉพาะกรณีมี unsaved import preview

#### UX priority ที่ควรจัดก่อน release

1. ทำ status/action mapping กลาง เพื่อให้ทุก role เห็น workflow ตรงกัน
2. ทำ score save flow ให้ transaction-safe และมี feedback ชัด
3. เคลียร์ admin readonly policy แล้วสะท้อนใน UI ให้ตรงกับ business rule
4. ย้าย Tailwind CDN ออกจาก production layout
5. เพิ่ม E2E fixture สำหรับ subject import full flow
6. ทำ empty state และ form validation ให้เป็น pattern เดียวกันทั้งระบบ
7. ตรวจ modal accessibility ด้วย keyboard และ screen reader path

### ลำดับแนะนำหลัง Jui update

1. เพิ่ม regression tests ให้ public report APIs ต้อง deny guest ทุก write/read endpoint ที่สำคัญ
2. ทำ test Docker profile ให้รัน Pest/Playwright ได้ reproducible โดยไม่ใช้ env `local`
3. แก้หรือยืนยัน policy ของ admin readonly และ status state machine
4. แก้ score save flow ให้ transaction-safe และมี failure tests
5. ย้าย Tailwind CDN เข้า build pipeline
6. Rotate/remove secrets ใน `.env.docker`
7. สร้าง E2E fixtures สำหรับ subject Excel import และ assignment/evaluation workflow เต็มวงจร
8. Triage npm vulnerabilities ก่อน release
