# Clear Facilitator Language Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ปรับบทพูดวิทยากรทั้งสองชุดให้เป็นภาษาพูดที่ชัดเจนและตรวจสอบไฟล์นำเสนอฉบับสมบูรณ์

**Architecture:** ใช้สคริปต์สร้าง PowerPoint เดิมเป็นแหล่งข้อมูลหลัก แก้เฉพาะข้อความใน `SCRIPTS` แล้วสร้างไฟล์ทั้งสองชุดใหม่ด้วย `@oai/artifact-tool` จากนั้นตรวจ OOXML ของโน้ตและเทียบภาพเรนเดอร์กับฉบับก่อนแก้ไข

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint COM สำหรับเรนเดอร์ตรวจสอบ, PowerShell สำหรับตรวจ OOXML

## Global Constraints

- คงหน้าสไลด์และรูปแบบเดิมทั้งหมด
- คง 18 สไลด์และ 18 ชุดโน้ตต่อไฟล์
- ทุกหน้าต้องมี `[พูด]` และ `[Sources]`
- ลำดับการอบรมต้องเป็นนำเสนอให้จบก่อน แล้วจึงสาธิตต่อเนื่อง

---

### Task 1: ปรับบทพูดที่กำกวม

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: `SCRIPTS.academic` และ `SCRIPTS.support`
- Produces: ข้อความ speaker notes ที่ใช้สร้าง PowerPoint ทั้งสองชุด

- [ ] **Step 1: ตรวจบทพูดทั้ง 36 หน้าและระบุคำกำกวม**
- [ ] **Step 2: แทนข้อความด้วยภาษาพูดที่บอกลำดับและหน้าที่อย่างชัดเจน**
- [ ] **Step 3: ตรวจว่าไม่มีคำกำกวมเดิมหลงเหลือในสคริปต์**

### Task 2: สร้างและตรวจไฟล์ PowerPoint

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: สคริปต์บทพูดที่ปรับแล้ว
- Produces: PowerPoint สองชุดพร้อม speaker notes ฉบับอ่านง่าย

- [ ] **Step 1: สร้างไฟล์ทั้งสองชุดด้วยโมดูล JavaScript เดิม**
- [ ] **Step 2: ตรวจจำนวนสไลด์ โน้ต `[พูด]` `[Sources]` และลำดับสาธิต**
- [ ] **Step 3: เรนเดอร์ครบทุกหน้าและเทียบกับฉบับก่อนแก้ไข**
- [ ] **Step 4: ตรวจวัตถุหลุดขอบและลบไฟล์ตรวจสอบชั่วคราว**
- [ ] **Step 5: บันทึกเฉพาะไฟล์ที่เกี่ยวข้องใน Git**

