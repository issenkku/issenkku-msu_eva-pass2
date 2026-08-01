# Training Decks Full Name and Copy Clarity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ใช้ชื่อเต็มของระบบในจุดแนะนำครั้งแรก และแก้สองประโยคที่ฟังแล้วสับสนใน PowerPoint อบรมทั้งสองชุด

**Architecture:** แก้ข้อความที่มองเห็นและ Speaker Notes ผ่านสคริปต์สร้าง PowerPoint เดิม จากนั้นสร้าง PPTX ใหม่ด้วย `@oai/artifact-tool` และตรวจทั้ง OOXML, ภาพเรนเดอร์ และการเปิดผ่าน PowerPoint

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint COM, PowerShell, OOXML/ZIP inspection

## Global Constraints

- ชื่อเต็มคือ `ระบบประเมินผลการปฏิบัติงานบุคลากร (MSU EVA)`
- ใช้ชื่อเต็มบนหน้าปกและ Speaker Notes หน้า 1 ของแต่ละชุด
- หลังจากกล่าวชื่อเต็มแล้ว ใช้คำว่า `ระบบ`
- ไม่ใช้ข้อความ `แยกให้ออกว่าเมื่อไรควรบันทึกร่างและเมื่อไรจึงควรส่งแบบประเมิน`
- ไม่ใช้ข้อความ `คนที่ปิดงานคือคนสุดท้ายในลำดับของงาน`
- รักษา 18 สไลด์ต่อชุด รูปแบบ สี ลำดับ และจังหวะสาธิตเดิม
- รักษาข้อเท็จจริงว่า Admin เป็นผู้กำหนดลำดับผู้ประเมิน

---

### Task 1: ปรับชื่อระบบบนหน้าปกและบทพูดเปิด

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: ฟังก์ชันสร้างหน้าปกและ `SCRIPTS.academic[0]`, `SCRIPTS.support[0]`
- Produces: หน้าปกและ Notes หน้า 1 ที่ใช้ชื่อเต็ม

- [ ] **Step 1: ตรวจข้อความเดิม**

ค้นหาคำว่า `MSU EVA` ในชื่อหน้าปกและบทพูดหน้า 1 ของทั้งสองชุด

- [ ] **Step 2: ปรับหน้าปก**

ใช้หัวข้อหลัก:

```text
ระบบประเมินผลการปฏิบัติงานบุคลากร
```

และคง `(MSU EVA)` เป็นชื่อย่อในบรรทัดรอง โดยรักษาการแบ่งสายวิชาการและสายสนับสนุนให้เห็นชัด

- [ ] **Step 3: ปรับ Notes หน้า 1**

เริ่มการแนะนำด้วย:

```text
ขอต้อนรับเข้าสู่การอบรมการใช้งานระบบประเมินผลการปฏิบัติงานบุคลากร หรือ MSU EVA
```

ประโยคถัดไปใช้คำว่า `ระบบ`

- [ ] **Step 4: ตรวจผลข้อความ**

ยืนยันว่าทั้งสองชุดมีชื่อเต็มบนหน้าปกและ Notes หน้า 1 อย่างละหนึ่งครั้ง โดยไม่กล่าวชื่อเต็มซ้ำในทุกหน้า

---

### Task 2: แก้สองประโยคที่ฟังแล้วสับสน

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: อาร์เรย์บทพูดฉบับวิทยากร
- Produces: บทพูดที่ใช้เงื่อนไขและผู้กระทำชัดเจน

- [ ] **Step 1: แก้ประโยคเรื่องบันทึกร่าง**

ใช้ข้อความ:

```text
ถ้าข้อมูลยังไม่ครบ ให้บันทึกร่างไว้ก่อน เมื่อข้อมูลและหลักฐานครบแล้วจึงส่งแบบประเมินครับ
```

- [ ] **Step 2: แก้ประโยคเรื่องปิดงาน**

ใช้ข้อความ:

```text
งานจะเสร็จสมบูรณ์เมื่อผู้ประเมินลำดับสุดท้ายกดยืนยันผลครับ
```

เพิ่มคำอธิบายต่อท้ายได้ว่า ผู้ประเมินลำดับสุดท้ายขึ้นอยู่กับลำดับที่ Admin กำหนดในงานนั้น

- [ ] **Step 3: ตรวจทุก Notes**

ยืนยันว่าไม่พบข้อความเดิมทั้งสองประโยค และไม่พบคำที่มีความหมายเดียวกันในรูปแบบ `คนที่ปิดงาน` หรือ `แยกให้ออกว่า`

---

### Task 3: สร้าง ตรวจ และบันทึก PowerPoint

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: สคริปต์ที่ปรับใน Task 1–2
- Produces: PowerPoint ฉบับวิทยากรสองชุด

- [ ] **Step 1: ตรวจไฟล์ล็อก**

ค้นหา `~$MSU-EVA-*-ฉบับวิทยากร.pptx` หากพบให้หยุดและขอให้ผู้ใช้ปิด PowerPoint

- [ ] **Step 2: สร้างไฟล์ใหม่**

นำเข้าโมดูลผ่าน Node REPL และยืนยันว่าทั้งสองชุดสร้างได้ชุดละ 18 สไลด์

- [ ] **Step 3: ตรวจ OOXML**

ต่อหนึ่งไฟล์ต้องพบ:

```text
slides=18
notes=18
[พูด]=18
[Sources]=18
```

พร้อมชื่อเต็มในหน้าปกและ Notes หน้า 1 และไม่พบสองประโยคเดิม

- [ ] **Step 4: ตรวจจังหวะสาธิต**

ยืนยันจำนวนเดิม:

```text
academic: [เปลี่ยนไปสาธิตระบบ]=5, [สาธิต]=5, [กลับมานำเสนอ]=5
support:  [เปลี่ยนไปสาธิตระบบ]=4, [สาธิต]=4, [กลับมานำเสนอ]=4
```

- [ ] **Step 5: เรนเดอร์และตรวจภาพ**

เรนเดอร์ครบ 36 หน้า ตรวจข้อความหน้าปกไม่ล้น ไม่ตัดคำผิดปกติ และไม่มีวัตถุอยู่นอกกรอบ

- [ ] **Step 6: Commit**

เพิ่มเฉพาะ PowerPoint สองไฟล์:

```powershell
git add -- `
  'docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx' `
  'docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx'
git commit -m 'docs: use full system name in training decks'
```
