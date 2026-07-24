# Split Role-Based MSU EVA Training Decks — Design Specification

## Decision

Replace the single combined training presentation with two independent Thai-language decks:

1. Morning: academic personnel
2. Afternoon: support personnel

The decks share one branded visual system but have different learning paths, examples, and accent colors. They must look like presentation material for a facilitated workshop, not a condensed report or user manual.

## Purpose and Delivery Context

- Format: 16:9 PowerPoint
- Delivery: projected presentation, live system demonstration, and hands-on practice
- Audience: personnel who may be using MSU EVA for the first time
- Language: Thai; retain an English system term only when it appears in the interface, such as `Completed`
- Target length: 13–14 slides per deck
- Presentation density: one clear teaching point per slide, with readable projection-sized text

## Shared Visual System

### Visual character

Use a polished “Professional Training” style: institutional credibility with the energy and clarity of a modern workshop.

- Spacious editorial compositions, not repeated card grids
- Strong page titles that state the teaching point
- Large diagrams, annotated examples, and application views
- Clear visual rhythm: cover, orientation, section divider, demonstration, practice, close
- Minimal body copy; detailed facilitation notes belong in speaker notes

### Brand palette

- Mahasarakham purple: primary institutional color
- Faculty gold: highlight and navigation accent
- Deep navy: headings and high-contrast backgrounds
- Warm white: main canvas
- Academic accent: indigo/blue
- Support accent: teal, with restrained orange for score and warning emphasis

Exact shades may be adjusted during production to maintain contrast and to harmonize with the supplied logos.

### Brand assets

- Faculty of Public Health, Mahasarakham University seal from `public/favicon-msu.png`
- IntelliGen Software Solutions logo from `C:\Users\pisut\Downloads\logoบริษัท.svg`
- The faculty seal is the primary institutional mark on covers and major section dividers.
- The company logo is secondary: smaller on covers, closing slides, or footers as the system developer.
- Do not stretch, crop, recolor, or place either logo on visually noisy backgrounds.

### Typography and layout

- Use a Thai sans-serif family available in the presentation environment, with consistent weight hierarchy.
- Minimum projected body size: 17 pt; aim for 20–24 pt on instructional slides.
- Titles: 28–36 pt depending on composition.
- Use a consistent top or side navigation device to identify the current phase of the workflow.
- Use rounded geometry sparingly for steps, callouts, and status labels.

### Image treatment

- Prefer real, sanitized application screenshots when they clarify a click path.
- Crop screenshots to the relevant region and annotate with numbered markers rather than showing an entire dense interface.
- Never show real credentials, personal information, or production data.
- Where a screenshot is unavailable or unnecessary, use native PowerPoint workflow diagrams and worked examples.
- Decorative imagery, if used, must support the training context and not compete with the interface.

## Shared Teaching Pattern

Both decks follow the same learning rhythm:

1. See the complete workflow.
2. Recognize the participant's own responsibility.
3. Watch the task performed in the system.
4. Understand the irreversible decision point.
5. Practice one complete task.
6. Verify that the work reached the expected status.

Shared content is adapted to the role instead of duplicated verbatim.

## Deck A — Morning: Academic Personnel

Working file: `docs/MSU-EVA-อบรมสายวิชาการ.pptx`

### Learning outcome

Participants can open an assigned academic evaluation, enter the required academic workload and evidence, check calculated results, save a draft, submit correctly, and recognize the configured review path through completion.

### Slide outline

1. **Cover** — การใช้งานระบบ MSU EVA สำหรับสายวิชาการ
2. **Session promise** — เช้านี้ทำงานหนึ่งรายการให้ครบตั้งแต่เริ่มจนพร้อมส่ง
3. **Academic workflow map** — ภาพรวมจากงานที่ได้รับมอบหมายถึง `Completed`
4. **Know your role** — ผู้รับการประเมินทำอะไร และงานส่งต่อให้ใคร
5. **Start at the dashboard** — เลือกรอบประเมินและรายการที่ถูกต้อง
6. **Academic data structure** — รายวิชา ภาระงาน และผลงานเชื่อมกันอย่างไร
7. **Enter quantity data** — ตัวอย่างการกรอกและการตรวจค่าที่ระบบคำนวณ
8. **Enter quality data** — ผลงาน เกณฑ์ และรายละเอียดที่ผู้ประเมินต้องเห็น
9. **Evidence that reviewers can verify** — เช็กลิสต์หลักฐานและลิงก์
10. **Draft or submit?** — เปรียบเทียบการบันทึกร่างกับการส่งประเมิน
11. **Review sequence** — ผู้ประเมินแต่ละลำดับตรวจ ให้คะแนน และส่งต่อ
12. **How work is closed** — ผู้ตรวจลำดับสุดท้ายรับรองจนเป็น `Completed`
13. **Hands-on mission** — ภารกิจทดลองทำหนึ่งรายการ พร้อมจุดตรวจความสำเร็จ
14. **Troubleshooting and help** — ปัญหาที่พบบ่อย คู่มือ และช่องทางช่วยเหลือ

### Signature visuals

- A horizontal academic workflow ribbon used as a recurring orientation device
- A clean subject/workload relationship diagram
- A split-screen worked example for quantity versus quality
- A prominent “Draft / Submit” decision slide
- A practice checklist that participants can follow without reading speaker notes

## Deck B — Afternoon: Support Personnel

Working file: `docs/MSU-EVA-อบรมสายสนับสนุน.pptx`

### Learning outcome

Participants can open an assigned support evaluation, record activities and indicator results, understand the weighted-score calculation, attach verifiable evidence, save a draft, submit correctly, and recognize the configured review path through completion.

### Slide outline

1. **Cover** — การใช้งานระบบ MSU EVA สำหรับสายสนับสนุน
2. **Session promise** — บ่ายนี้ทำกิจกรรมหนึ่งรายการให้ครบและเห็นที่มาของคะแนน
3. **Support workflow map** — ภาพรวมจากงานที่ได้รับมอบหมายถึง `Completed`
4. **Know your role** — ผู้รับการประเมินทำอะไร และงานส่งต่อให้ใคร
5. **Start at the dashboard** — เลือกรอบประเมินและรายการที่ถูกต้อง
6. **Support data structure** — โครงการ กิจกรรม ตัวชี้วัด และหลักฐานเชื่อมกันอย่างไร
7. **Record activity results** — กรอกเป้าหมาย ผลที่ทำได้ และรายละเอียดกิจกรรม
8. **Understand the weighted score** — `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100`
9. **Worked score example** — ตัวอย่างตัวเลขจากข้อมูลตั้งต้นถึงคะแนนสุดท้าย
10. **Evidence before submission** — เพิ่มรายการกิจกรรมและลิงก์หลักฐานที่ตรวจสอบได้
11. **Draft or submit?** — จุดที่ยังแก้ไขได้ เงื่อนไขที่ขวางการส่ง และผลหลังส่ง
12. **Review and close** — ลำดับผู้ประเมินและผู้ตรวจลำดับสุดท้าย
13. **Hands-on mission** — ภารกิจทดลองกรอกกิจกรรม คำนวณคะแนน และแนบหลักฐาน
14. **Troubleshooting and help** — ปัญหาที่พบบ่อย คู่มือ และช่องทางช่วยเหลือ

### Signature visuals

- A support workflow ribbon with a distinct teal accent
- A project–activity–indicator relationship diagram
- A large formula slide followed by a numeric worked example
- A visible evidence gate before submission
- A practice checklist focused on score and evidence verification

## Accuracy Rules

- Do not imply that an executive always closes the work.
- State that the last configured reviewer closes the work.
- Use role terms consistently: ผู้รับการประเมิน, ผู้ประเมิน/หัวหน้างาน, กรรมการ, ผู้บริหาร.
- Explain the support weighted-score formula exactly as shown by the system:
  `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100`
- Make the difference between saving a draft and submitting unmistakable.
- State evidence requirements only where they are enforced by the system.
- Do not invent interface controls, approval steps, or policy claims.

## Production and Verification

- Build both presentations from scratch; do not reuse the prior sparse Codex Grid treatment.
- Add sources and facilitation notes to speaker notes where required.
- Render every slide and visually inspect it at full size.
- Generate montage views to confirm that the two decks feel related but remain easy to distinguish.
- Run layout checks for clipping, overflow, unintended overlaps, title wrapping, and missing glyphs.
- Verify logo clarity, contrast, and visual hierarchy on covers and closing slides.
- Preserve all unrelated repository changes.
