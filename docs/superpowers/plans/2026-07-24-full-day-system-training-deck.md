# Full-Day System Training Deck Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build and verify a 15-slide Thai PowerPoint deck for morning academic-personnel training and afternoon support-personnel training in MSU EVA.

**Architecture:** Use `@oai/artifact-tool` from a plain JavaScript ES module in an external scratch workspace. Adapt selected Codex Grid compositions, source application truth from the repository, and export one 16:9 PPTX to `docs/MSU-EVA-training-academic-support-th.pptx`. Render every slide and run overflow validation before delivery.

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint (`.pptx`), bundled presentation rendering and validation tools

## Global Constraints

- The deck contains exactly 15 audience-facing slides in Thai.
- Morning content covers academic personnel; afternoon content covers support personnel.
- The last configured reviewer closes the work; the deck must not imply that the executive always closes it.
- The support formula is `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100`.
- Required evidence must be attached before submission.
- Use the bundled Codex Grid layout library as the composition reference.
- Use a restrained purple accent consistent with the application UI.
- Use authentic application content only; do not expose credentials or personal data.
- Final output is `docs/MSU-EVA-training-academic-support-th.pptx`.
- Preserve unrelated existing repository changes.

---

## File Structure

- Create: external scratch `<temp>/codex-presentations/<thread>/msu-eva-training/tmp/build-deck.mjs` — presentation source and slide composition.
- Create: external scratch `<temp>/codex-presentations/<thread>/msu-eva-training/tmp/source-notes.txt` — repository source and asset provenance.
- Create: external scratch `<temp>/codex-presentations/<thread>/msu-eva-training/tmp/qa/qa-ledger.txt` — per-slide visual review record.
- Create: `docs/MSU-EVA-training-academic-support-th.pptx` — final user deliverable.
- Use without modification: `docs/user-guide-th.md`, `app/Support/AssignmentFlow.php`, `resources/views/criteria_config/partials/support-criteria-template.blade.php`, and relevant existing training decks.

### Task 1: Prepare Content and Visual Assets

**Files:**
- Create: external scratch `tmp/source-notes.txt`
- Create: external scratch `tmp/assets/`
- Read: `docs/user-guide-th.md`
- Read: `app/Support/AssignmentFlow.php`
- Read: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Read: `docs/user-guide-th.pptx`
- Read: `docs/ทดสอบระบบสายวิชาการ-2026-07-22.pptx`

**Interfaces:**
- Consumes: approved design in `docs/superpowers/specs/2026-07-24-full-day-system-training-deck-design.md`
- Produces: verified Thai slide copy, selected Codex Grid layout references, and reusable local screenshot/image assets

- [ ] **Step 1: Initialize the external artifact-tool workspace**

Run:

```powershell
node "$env:SKILL_DIR\container_tools\setup_artifact_tool_workspace.mjs" --workspace "$env:TMP_DIR"
```

Expected: the scratch `tmp` workspace contains package metadata that resolves `@oai/artifact-tool`.

- [ ] **Step 2: Inspect the approved content sources**

Read the repository sources listed above and record only claims needed by the 15-slide plan, including:

```text
Review sequence: configured dynamically from evaluator, director, and manager stages
Final state: Completed
Support formula: คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100
Submission warning: after confirmation, the form becomes read-only
```

Expected: `tmp/source-notes.txt` contains no credentials, personal data, placeholders, or unsupported claims.

- [ ] **Step 3: Inspect Codex Grid references**

Read:

```text
builtin_templates_support/codex-grid-layout-library/ARTIFACT.md
assets/builtin_templates/codex-grid-layout-library/design_tokens.json
assets/builtin_templates/codex-grid-layout-library/artifact-tool-compose/template-registry.json
assets/builtin_templates/codex-grid-layout-library/assets/previews/layout-library.png
```

Select a varied set of title, process, text-and-image, comparison, and closing layouts whose density and typography budgets can hold the approved Thai copy.

Expected: the selected layouts cover all 15 slides without repeating one silhouette more than three times consecutively.

- [ ] **Step 4: Extract only useful authentic visuals**

Render the two existing training decks, inspect their slides, and extract or recreate only screenshots that clearly show a click path required by the new deck.

Expected: every retained screenshot is legible, contains no sensitive information, and is used no more than once.

- [ ] **Step 5: Review the task deliverable**

Confirm:

```text
15 slide claims mapped
Academic and support paths distinct
Shared reviewer/closing path accurate
All assets local and traceable
No sensitive data
```

Expected: all five checks pass.

### Task 2: Author the 15-Slide PowerPoint

**Files:**
- Create: external scratch `tmp/build-deck.mjs`
- Create: `docs/MSU-EVA-training-academic-support-th.pptx`

**Interfaces:**
- Consumes: Task 1 slide copy, layout shortlist, and visual assets
- Produces: a 16:9 PowerPoint deck with exactly 15 slides

- [ ] **Step 1: Implement the deck shell**

In `tmp/build-deck.mjs`, import `@oai/artifact-tool`, define the 16:9 presentation, theme tokens, Thai-capable fonts, purple accent, footer treatment, and shared helpers for titles and page markers.

Expected: running the module creates a valid PowerPoint with one test slide and no unresolved imports.

- [ ] **Step 2: Implement slides 1–6**

Create:

```text
1 การอบรมการใช้งานระบบ MSU EVA
2 วันนี้ทุกคนจะทำงานหนึ่งรายการจนจบกระบวนการ
3 แต่ละบทบาทรับช่วงงานต่อกัน
4 งานเดินตามลำดับที่ผู้ดูแลระบบกำหนด
5 บันทึกร่างแก้ไขได้ แต่ส่งแล้วแก้ไขไม่ได้
6 เริ่มจากแดชบอร์ดและเลือกงานที่ได้รับมอบหมาย
```

Expected: the opening establishes the training outcome, shared role language, dynamic sequence, and irreversible submission decision.

- [ ] **Step 3: Implement slides 7–10**

Create:

```text
7 สายวิชาการเริ่มจากรายวิชาและภาระงาน
8 กรอกผลงาน ตรวจสูตร และแนบหลักฐาน
9 สายสนับสนุนเริ่มจากกิจกรรมและตัวชี้วัด
10 คะแนนถ่วงน้ำหนักเกิดจากน้ำหนักและผลงานที่ทำได้
```

On slide 10, show:

```text
คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100
ตัวอย่าง: 20 × 80 ÷ 100 = 16 คะแนน
```

Expected: participants can distinguish the two data-entry paths and understand the support-score example.

- [ ] **Step 4: Implement slides 11–15**

Create:

```text
11 ผู้ประเมินแต่ละลำดับตรวจ ให้คะแนน และส่งต่อ
12 ผู้ตรวจลำดับสุดท้ายรับรองจนสถานะเป็น Completed
13 งานที่ปิดแล้วพร้อมดูผลและส่งออกรายงาน
14 หากติดขัด ให้ตรวจสถานะ สิทธิ์ และข้อมูลที่บังคับก่อน
15 ลองทำหนึ่งงานให้ครบ แล้วใช้คู่มือเมื่อกลับไปทำงานจริง
```

Expected: the ending resolves the opening promise with a hands-on challenge rather than a generic thank-you slide.

- [ ] **Step 5: Export and inspect presentation structure**

Run the module and inspect the resulting deck metadata.

Expected:

```text
File: docs/MSU-EVA-training-academic-support-th.pptx
Slides: 15
Aspect ratio: 16:9
No unresolved placeholder text
```

- [ ] **Step 6: Review the task deliverable**

Check every visible text element against the approved spec and confirm no timing scaffolds, production notes, credentials, or internal implementation language appears on slides.

Expected: all audience-facing copy is concise Thai suitable for projection.

### Task 3: Render, Validate, and Deliver

**Files:**
- Create: external scratch `tmp/preview/`
- Create: external scratch `tmp/qa/qa-ledger.txt`
- Validate: `docs/MSU-EVA-training-academic-support-th.pptx`

**Interfaces:**
- Consumes: Task 2 PowerPoint
- Produces: visually inspected and mechanically validated final deck

- [ ] **Step 1: Render all slides**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\render_slides.py" "docs/MSU-EVA-training-academic-support-th.pptx"
```

Expected: 15 PNG files, one per slide.

- [ ] **Step 2: Create the overview montage**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\create_montage.py" --input_dir "docs/MSU-EVA-training-academic-support-th" --output_file "$env:TMP_DIR\qa\montage.png"
```

Expected: one montage showing consistent deck flow and varied composition.

- [ ] **Step 3: Inspect every slide individually**

At full size, record pass/fail for:

```text
Title fits on one line where intended
No text clipping or unexpected wrapping
No unintended overlaps
Screenshots remain legible
Formula and example are correct
Page markers and visual accents are consistent
```

Expected: `tmp/qa/qa-ledger.txt` contains one completed record for each of 15 slides.

- [ ] **Step 4: Run mechanical validation**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\slides_test.py" "docs/MSU-EVA-training-academic-support-th.pptx"
```

Expected: no slide content overflows the original slide canvas.

- [ ] **Step 5: Fix and repeat validation**

For each failed visual or mechanical check, shorten copy or adjust the composition in `tmp/build-deck.mjs`, regenerate the deck, and repeat Steps 1–4.

Expected: all 15 visual records pass and the validator exits successfully.

- [ ] **Step 6: Inspect final repository state**

Run:

```powershell
git status --short
git diff --check
```

Expected: the final PPTX and approved documentation are the only task-owned additions; all unrelated pre-existing changes remain untouched.

- [ ] **Step 7: Commit the final deliverable**

Run:

```powershell
git add docs/MSU-EVA-training-academic-support-th.pptx
git commit -m "docs: add academic and support system training deck"
```

Expected: one commit containing only the final PowerPoint.

