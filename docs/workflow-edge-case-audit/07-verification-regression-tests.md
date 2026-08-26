# Epic G — Verification และ Regression Tests

## เป้าหมาย

เอกสารนี้สรุป test gaps ที่ควรเพิ่มเพื่อปิดข้อผิดพลาดที่พบใน workflow audit

## G-01 — EvaluationFlow contract tests

Priority: `P0`

ควรเพิ่ม test กลางที่ assert ว่า status vocabulary และ transition เหมือนกันทุก Module

### กรณีที่ควรมี

- default flow: evaluatee → evaluator → director → manager → completed
- custom flow: evaluatee → director → manager
- custom flow: evaluatee → manager
- scheduler expire assigned/draft ใช้ first stage จาก `evaluation_flow`
- `Manager_assign` อยู่ group เดียวกันใน dashboard/query/graph
- lowercase/uppercase status ถูก reject หรือ normalize อย่างชัดเจน

## G-02 — ScorePersistence contract tests

Priority: `P0`

ควรเพิ่ม test ผ่าน Interface กลางของการ save score แทน test controller แบบ full payload อย่างเดียว

### กรณีที่ควรมี

- partial payload ไม่ลบ score เดิม ถ้า operation เป็น patch
- replace mode ต้องส่ง payload ครบ ไม่เช่นนั้น reject
- description-only quantity row preserve ข้าม role
- evidence patch ไม่ลบ link เดิมโดยไม่ explicit delete
- duplicate support criterion id ต้อง validation fail
- history ถูกสร้างเฉพาะเมื่อ value เปลี่ยนจริง

## G-03 — AssignmentLifecycle destructive-update tests

Priority: `P0`

### กรณีที่ควรมี

- update assignment ที่มี report `Assigned` แต่ยังไม่มี score
- update assignment ที่มี score/evidence/workload แล้ว
- update assignment ที่ `Completed`
- copy assignment ต้องไม่แตะ report เดิม
- update metadata ต้อง preserve report graph

## G-04 — Authorization tests

Priority: `P0/P1`

### กรณีที่ควรมี

- evaluator_id ตรง user A
- user B มี position เดียวกันแต่ไม่ใช่ evaluator_id
- verify user B view/edit ได้หรือไม่ได้ตาม policy ที่ตกลง
- director/manager ใช้ policy เดียวกับ evaluator
- readonly access แยกจาก edit access

## G-05 — Evidence display isolation tests

Priority: `P1`

### กรณีที่ควรมี

- quality evidence แสดงเฉพาะ quality context
- support evidence แสดงเฉพาะ support context
- workload evidence แสดงเฉพาะ workload entry
- evidence ใน evaluation_list เดียวกันแต่คนละ purpose ไม่ปะปน

## G-06 — Report read model consistency tests

Priority: `P1`

### กรณีที่ควรมี

- total score ใน detail/dashboard/export ตรงกันตาม policy
- support raw/display/capped values ตรงตาม contract
- role comments ทั้งหมดปรากฏใน read model/export
- orphan report คืน controlled error state
- malformed date ไม่ทำให้ dashboard ล้ม

## G-07 — Criteria config tests

Priority: `P1/P2`

### กรณีที่ควรมี

- omit `support_criterias` key ใน edit ไม่ลบ row เดิม
- explicit delete support criteria เท่านั้นที่ลบจริง
- duplicate support criteria payload reject
- support sequence unique ต่อ evaluation list
- create/edit serializer ให้ error format เหมือนกัน
- move up/down button ทำงานทั้ง create/edit
- long rich text responsive view ไม่ clip action/input

## G-08 — UI/accessibility tests

Priority: `P2`

### กรณีที่ควรมี

- rendered page ไม่มี duplicate IDs ใน composed Blade
- confirmation modal มี `role="dialog"`, `aria-modal`, focus trap, focus restore
- loading state ไม่ใช้ fixed timer เป็น source of truth
- double submit ถูก block
- flash message มี live region
- reduced motion preference respected
- contrast critical states ผ่าน threshold

## G-09 — Subject import test infrastructure

Priority: `P2`

### กรณีที่ควรมี

- แยก schema-mutating tests ออกจาก suite ปกติ
- isolate SQLite connection ต่อ test/process
- detect long-running PHP process ที่ lock testing DB
- document safe reset command
- run subject import E2E ทั้ง batch และ isolated เพื่อจับ test interference

