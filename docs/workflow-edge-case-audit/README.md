# Workflow Edge Case Audit

วันที่ตรวจ: 2026-07-20

เอกสารชุดนี้บันทึกข้อผิดพลาดและ condition conflict ที่พบจากการตรวจระบบแบบ read-only เพื่อใช้เป็น backlog สำหรับแก้ในอนาคต โดยจัดกลุ่มตาม Epic/Module และ workflow หลักของระบบประเมิน

## ขอบเขตการตรวจ

- ตรวจจาก source code, tests, docs ใน repo
- ใช้ subagent สำหรับ explore แยก workflow และสรุปผลรวม
- ไม่ได้แก้ source code
- ไม่ได้ยืนยันด้วย browser E2E จริง
- ไม่มี `CONTEXT.md`, `docs/adr/`, `PRODUCT.md` ให้เทียบ intent เชิงธุรกิจเต็มรูปแบบ

## สถานะ verification

- `npm run test:js` ผ่าน `7/7`
- `vendor\bin\pest` ชุด evaluation/report/subject/support รอบแรกผ่าน `142` และล้ม `12`
- reset testing DB ด้วย `php artisan migrate:fresh --env=testing --force`
- rerun subject/UI slice ผ่าน `39` และล้ม `14`
- subject import test ที่แยกรันเดี่ยวผ่าน `1/1`
- สรุป: failure กลุ่ม subject import ชี้ไปที่ test isolation / SQLite lock / schema state fragility มากกว่ายืนยัน product defect โดยตรง

## Severity

- `P0` — เสี่ยงล้มทันที, data loss, authorization กว้างเกิน, หรือ block release
- `P1` — condition ข้าม workflow ไม่สอดคล้อง ทำให้ข้อมูล/สถานะ/คะแนนผิดเพี้ยน
- `P2` — UX, accessibility, test coverage, หรือ consistency issue ที่ควรแก้ก่อน hardening

## Epic / Module Index

| Epic | Workflow | File |
|---|---|---|
| A | Assignment และ Evaluation Flow | [01-assignment-evaluation-flow.md](01-assignment-evaluation-flow.md) |
| B | Score Persistence และ Evidence | [02-score-persistence-evidence.md](02-score-persistence-evidence.md) |
| C | Report Read Model, Dashboard, Export | [03-report-read-dashboard-export.md](03-report-read-dashboard-export.md) |
| D | Criteria Config และ Support Criteria | [04-criteria-config-support-criteria.md](04-criteria-config-support-criteria.md) |
| E | Subject Import, Settings, User Management | [05-subject-import-settings-user-management.md](05-subject-import-settings-user-management.md) |
| F | UI State และ Accessibility | [06-ui-accessibility-state.md](06-ui-accessibility-state.md) |
| G | Verification และ Regression Tests | [07-verification-regression-tests.md](07-verification-regression-tests.md) |

## Top Recommendation

ควรเริ่มจากการ deepen สอง Module:

1. `EvaluationFlow` Module — Interface เดียวสำหรับ status vocabulary, transition, role authorization, custom flow, scheduler, dashboard progress
2. `ScorePersistence` Module — Interface เดียวสำหรับ patch/replace/delete score operation, evidence, comments, history, transaction invariants

สอง Module นี้ให้ Leverage สูงสุด เพราะลด condition conflict ที่กระจายอยู่ในหลาย controller และเพิ่ม Locality ของ bug fix/test surface
