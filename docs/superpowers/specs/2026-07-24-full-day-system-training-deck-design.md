# Full-Day System Training Deck Design

## Purpose

Create a Thai-language PowerPoint deck for a one-day hands-on MSU EVA training:

- Morning: academic personnel
- Afternoon: support personnel

The deck supports a facilitator-led demonstration and participant practice. It is not a replacement for the detailed user guide.

## Communication Job

By the end, academic and support personnel should be able to complete their own evaluation workflow, distinguish saving a draft from submitting, follow the configured review sequence, and verify that the final reviewer has closed the work as `Completed`.

## Audience and Use

- Primary audience: academic and support personnel who may be new to MSU EVA
- Secondary audience: evaluators, committee members, executives, and system administrators
- Delivery mode: projected presentation followed by live demonstration and hands-on practice
- Language: Thai, with internal status code `Completed` shown only where it helps users verify the final state

## Narrative

The presentation follows a learning progression:

1. Establish why the system and role sequence matter.
2. Show the shared workflow and the irreversible submission decision.
3. Teach the academic data-entry path.
4. Teach the support data-entry path.
5. Rejoin both paths at review, final approval, and report export.
6. End with a concrete practice challenge and support channels.

## Slide Plan

1. Title — การอบรมการใช้งานระบบ MSU EVA
2. Outcome — วันนี้ทุกคนจะทำงานหนึ่งรายการจนจบกระบวนการ
3. Roles — แต่ละบทบาทรับช่วงงานต่อกัน
4. Workflow — งานเดินตามลำดับที่ผู้ดูแลระบบกำหนด
5. Decision — บันทึกร่างแก้ไขได้ แต่ส่งแล้วแก้ไขไม่ได้
6. Entry point — เริ่มจากแดชบอร์ดและเลือกงานที่ได้รับมอบหมาย
7. Academic path — สายวิชาการเริ่มจากรายวิชาและภาระงาน
8. Academic evidence — กรอกผลงาน ตรวจสูตร และแนบหลักฐาน
9. Support path — สายสนับสนุนเริ่มจากกิจกรรมและตัวชี้วัด
10. Support scoring — คะแนนถ่วงน้ำหนักเกิดจากน้ำหนักและผลงานที่ทำได้
11. Review path — ผู้ประเมินแต่ละลำดับตรวจ ให้คะแนน และส่งต่อ
12. Close work — ผู้ตรวจลำดับสุดท้ายรับรองจนสถานะเป็น `Completed`
13. Export — งานที่ปิดแล้วพร้อมดูผลและส่งออกรายงาน
14. Recovery — ปัญหาที่พบบ่อยและวิธีตรวจเบื้องต้น
15. Practice close — ภารกิจทดลองหนึ่งงานและช่องทางคู่มือ/ช่วยเหลือ

## Visual Direction

- Use the bundled Codex Grid layout library as the composition reference.
- Use a clean, high-contrast training style with a restrained purple accent that aligns with the application UI.
- Prefer large takeaway titles, simple process visuals, and authentic application screenshots where they materially clarify a click path.
- Avoid dense dashboard-like card grids, decorative stock photography, and repeated screenshots.
- Use one simple workflow diagram. Other slides should use flat text-and-image compositions.
- Keep all audience-facing text concise enough for projection in a training room.

## Content Rules

- Do not imply that the executive always closes the work.
- State that the last configured reviewer closes the work.
- Use “ผู้รับการประเมิน”, “ผู้ประเมิน/หัวหน้างาน”, “กรรมการ”, and “ผู้บริหาร” consistently.
- Explain the support weighted-score formula as shown in the system:
  `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100`
- Emphasize that a required evidence link must be present before submission.
- Do not include credentials, production data, personal data, or unsupported claims.

## Deliverable and Verification

- Final file: `docs/MSU-EVA-training-academic-support-th.pptx`
- 16:9 widescreen PowerPoint
- Render and inspect every slide at full size
- Run overflow and layout validation
- Fix unintended overlaps, clipping, title wrapping, unresolved placeholders, and inconsistent typography
- Preserve all unrelated existing repository changes

