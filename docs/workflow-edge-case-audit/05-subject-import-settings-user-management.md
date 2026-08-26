# Epic E — Subject Import, Settings, User Management

## Workflow ที่เกี่ยวข้อง

1. Admin download subject import template
2. Admin upload workbook
3. ระบบอ่าน workbook และสร้าง preview snapshot
4. Admin confirm selected changed rows
5. ระบบตรวจ stale fingerprint แล้ว create/update subjects
6. Admin จัดการ subject/settings/user records

## Module / Interface ที่เกี่ยวข้อง

- `SubjectImportController`
- `SubjectWorkbookReader`
- `SubjectImportPreviewService`
- `SubjectImportCommitter`
- `SubjectImportSnapshotStore`
- `SubjectImportResultStore`
- `SubjectController`
- `UserController`
- `DepartmentsController`
- `PositionsController`
- `JobLevelsController`

## E-01 — Subject import product defect ยังไม่ยืนยัน แต่ test isolation มีปัญหาจริง

Severity: `P2` สำหรับ test infrastructure

### จุดที่พบ

- `tests/Feature/Subjects/SubjectImportEndToEndTest.php`
- `tests/Feature/Subjects/SubjectImportHttpTest.php`
- `database/testing.sqlite`

### การทำงานปัจจุบันจากผล test

เมื่อรัน subject/import tests เป็น batch พบ error เช่น:

- SQLite table missing
- schema column missing
- database locked
- disk image malformed ในรอบแรก

หลัง reset testing DB แล้ว test เดี่ยวที่เคยล้มผ่าน `1/1`

### สาเหตุที่ประเมินได้

มีความเป็นไปได้สูงว่า failure มาจาก test isolation / SQLite state / process lock ไม่ใช่ product defect ที่ยืนยันได้ เพราะ isolated test ผ่าน

### Failure mode

CI หรือ local verification อาจให้ผล false negative ทำให้ทีมไม่เชื่อ test suite หรือจับ regression จริงไม่ชัด

### Coverage gap

ยังไม่ได้แยก root cause ระหว่าง:

- test ที่ drop schema แล้วไม่ restore
- parallel process lock
- RefreshDatabase state reuse
- external PHP process ถือ connection

### แนวทางแก้

- แยก test ที่ mutate schema ออกจาก suite ปกติ หรือ restore schema หลัง test
- ใช้ in-memory SQLite ต่อ process ถ้า framework รองรับ
- ปิด parallel/long-running PHP process ก่อนรัน DB test
- เพิ่ม test infra note ใน docs

## E-02 — Subject import Module ค่อนข้าง deep แต่ยังต้องรักษา contract

Severity: `P2`

### จุดที่พบ

- `SubjectImportPreviewService`
- `SubjectImportCommitter`
- `SubjectImportSnapshotStore`

### การทำงานปัจจุบัน

Module นี้มี Interface ที่ค่อนข้างดี:

- preview แยก `new`, `changed`, `unchanged`, `errors`
- snapshot token scoped by owner
- confirm ใช้ `claimForUser`
- stale check ผ่าน fingerprint
- create/update อยู่ใน transaction

### Risk ที่ยังเหลือ

ถ้า fingerprint รวม field ที่ไม่เกี่ยวกับ import behavior มากเกินไป เช่น `sort_order` หรือ `updated_at` อาจทำให้ preview stale บ่อยเกินจำเป็น

### Failure mode

ผู้ใช้ preview แล้ว confirm ช้า อาจเจอ stale แม้ field ที่แก้ไม่เกี่ยวกับ workbook payload

### Coverage gap

มี stale test แล้ว แต่ยังไม่มี test แยกว่า field ไหนควร/ไม่ควรทำให้ stale

### แนวทางแก้

กำหนด `SubjectImportFingerprint` Interface ชัดเจนว่า field ใดเป็น load-bearing invariant

## E-03 — Bulk delete/settings มี guard บางส่วน แต่ยังควร contract test เพิ่ม

Severity: `P2`

### จุดที่พบ

- `SubjectController::bulkDestroy`
- `UserController::bulkDestroy`
- `DepartmentsController::bulkDestroy`
- `PositionsController::bulkDestroy`
- `JobLevelsController::bulkDestroy`

### การทำงานปัจจุบัน

มี test บางส่วนสำหรับ bulk delete และ last active admin guard

### Risk ที่ยังเหลือ

bulk operation หลาย Module ใช้ pattern คล้ายกัน แต่แต่ละ controller มี Implementation เอง

### Failure mode

กติกาเช่น:

- ห้ามลบ item ที่ถูกใช้งาน
- ห้ามลบ admin คนสุดท้าย
- preserve current user
- partial deletion พร้อม warning

อาจไม่สอดคล้องกันระหว่าง Module

### Coverage gap

ยังไม่มี shared contract test สำหรับ bulk operation semantics ทุก settings Module

### แนวทางแก้

สร้าง `BulkMutationPolicy` หรือ test matrix กลาง:

- protected records
- skipped records
- deleted records
- user-facing message
- transaction behavior

