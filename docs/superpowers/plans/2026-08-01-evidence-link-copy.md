# Evidence Link Copy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ปรับข้อความหลักฐานใน PowerPoint ให้สื่อว่าระบบรับลิงก์และต้องเปิดแล้วระบุรายการได้ชัดเจน

**Architecture:** แก้รายการที่สามใน `addEvidence` และ speaker notes สองหน้าในสคริปต์สร้างชุดเดิม จากนั้นสร้าง PowerPoint ใหม่และเทียบภาพทุกหน้าเพื่อยืนยันขอบเขตการเปลี่ยนแปลง

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint COM, PowerShell OOXML checks

## Global Constraints

- ใช้ข้อความ `ตรวจสอบได้ชัดเจน` และ `เปิดลิงก์แล้วรู้ว่าเป็นหลักฐานของรายการใด`
- ไม่กล่าวว่าระบบตั้งชื่อหรืออัปโหลดไฟล์ได้
- คง 18 สไลด์และ 18 ชุดโน้ตต่อไฟล์

---

### Task 1: แก้ข้อความและบทพูด

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: `addEvidence`, `SCRIPTS.academic[10]`, `SCRIPTS.support[11]`
- Produces: ข้อความสไลด์และบทพูดที่ตรงกับฟังก์ชันลิงก์หลักฐาน

- [ ] **Step 1: เปลี่ยนรายการที่สามใน `addEvidence`**
- [ ] **Step 2: ขยายพื้นที่หัวข้อให้ข้อความอยู่บรรทัดเดียว**
- [ ] **Step 3: แก้บทพูดสองหน้าให้กล่าวถึงการเปิดลิงก์และรายการที่รองรับ**

### Task 2: สร้างและตรวจไฟล์

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: สคริปต์สร้างชุดที่ปรับแล้ว
- Produces: PowerPoint สองชุดที่มีข้อความหลักฐานถูกต้อง

- [ ] **Step 1: สร้างไฟล์ทั้งสองชุดใหม่**
- [ ] **Step 2: ตรวจข้อความและโน้ตจาก OOXML**
- [ ] **Step 3: เรนเดอร์ครบทุกหน้าและตรวจหน้าที่แก้แบบเต็มหน้า**
- [ ] **Step 4: เทียบว่าหน้าอื่นไม่เปลี่ยนและตรวจวัตถุหลุดขอบ**
- [ ] **Step 5: ลบไฟล์ชั่วคราวและ commit เฉพาะไฟล์ที่เกี่ยวข้อง**

