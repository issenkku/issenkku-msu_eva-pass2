# Training Test Link Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่มลิงก์ทดสอบและคำชี้แจงก่อนติดตั้งจริงในสไลด์ฝึกปฏิบัติของ PowerPoint ทั้งสองชุด

**Architecture:** ปรับฟังก์ชัน `addPractice` และ speaker notes ในสคริปต์สร้างชุดเดิม แล้วสร้าง PowerPoint ใหม่ด้วย `@oai/artifact-tool` ตรวจสไลด์ 17 แบบเต็มหน้าและยืนยันว่าอีก 17 หน้าไม่เปลี่ยน

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint COM, PowerShell OOXML checks

## Global Constraints

- ใช้ URL `http://149.28.142.186/login` ตรงตามที่ผู้ใช้ให้มา
- เพิ่มเฉพาะสไลด์ 17 และโน้ตของสไลด์ 17
- คง 18 สไลด์และ 18 ชุดโน้ตต่อไฟล์
- คงลำดับนำเสนอจบก่อน แล้วจึงสาธิตและให้ทดลอง

---

### Task 1: เพิ่มเนื้อหาช่วงทดลอง

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: `addPractice`, `SCRIPTS.academic[16]`, `SCRIPTS.support[16]`
- Produces: สไลด์ 17 และ speaker notes ที่มี URL กับคำชี้แจง

- [ ] **Step 1: เพิ่ม URL และคำชี้แจงในฟังก์ชัน `addPractice`**
- [ ] **Step 2: ปรับตำแหน่งรายการฝึกปฏิบัติโดยไม่ทับส่วนอื่น**
- [ ] **Step 3: เพิ่ม URL และคำชี้แจงในบทพูดของทั้งสองชุด**

### Task 2: สร้างและตรวจไฟล์

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: สคริปต์สร้างชุดที่ปรับแล้ว
- Produces: PowerPoint ฉบับพร้อมใช้ในการอบรม

- [ ] **Step 1: สร้างไฟล์ PowerPoint ทั้งสองชุดใหม่**
- [ ] **Step 2: ตรวจ URL คำชี้แจง โน้ต และลำดับสาธิตจาก OOXML**
- [ ] **Step 3: เรนเดอร์ครบทุกหน้าและตรวจสไลด์ 17 แบบเต็มหน้า**
- [ ] **Step 4: เทียบภาพสไลด์ 1–16 และ 18 กับฉบับก่อนแก้ไข**
- [ ] **Step 5: ตรวจวัตถุหลุดขอบ ลบไฟล์ชั่วคราว และ commit เฉพาะไฟล์ที่เกี่ยวข้อง**

