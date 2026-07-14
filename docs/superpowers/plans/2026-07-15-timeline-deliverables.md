# Timeline Deliverables Summary Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a compact deliverables summary to slide 5 of the tailored five-slide kickoff deck and add its Thai speaking script to the facilitator runbook.

**Architecture:** Keep the user's existing five-slide PowerPoint intact and edit only slide 5 through PowerPoint COM automation. Protect the binary change with OOXML assertions in the existing Node test, and update the runbook with the approved one-paragraph script while preserving the user's unstaged opening-line edit.

**Tech Stack:** PowerPoint COM, Node.js, JSZip, Node test runner, Markdown.

## Global Constraints

- Slide 5 remains part of the tailored five-slide deck; do not restore slide 6.
- Add a light summary bar below the timeline without changing existing timeline elements.
- Use the exact slide text: `สิ่งส่งมอบหลัก` and `Source Code และฐานข้อมูล • คู่มือผู้ใช้งาน • ผลการทดสอบ UAT • การติดตั้ง อบรม และดูแลหลังขึ้นระบบ`.
- Use the exact speaking script approved in `docs/superpowers/specs/2026-07-14-kickoff-system-preview-design.md`.
- Do not edit the PowerPoint while a `~$` lock file exists.
- Preserve the user's unstaged opening-line edit in `docs/kickoff/2026-07-15-facilitator-runbook.md`.
- Do not modify the untracked UAT DOCX or `scripts/generate-uat-summary-docx.py`.

---

### Task 1: Add the slide 5 deliverables summary and speaking script

**Files:**
- Modify: `tests/js/kickoff-package.test.mjs:76-95`
- Modify: `docs/kickoff/2026-07-15-facilitator-runbook.md:143-154`
- Modify: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx` (slide 5 only)

**Interfaces:**
- Consumes: the five-slide tailored PowerPoint and the approved wording in `docs/superpowers/specs/2026-07-14-kickoff-system-preview-design.md`.
- Produces: slide 5 OOXML containing the deliverables heading/items and a runbook paragraph containing the same speaking script.

- [ ] **Step 1: Write the failing test**

Extend the existing tailored-PowerPoint test:

```js
const slide5 = await archive.file('ppt/slides/slide5.xml').async('string');

assert.match(slide5, /สิ่งส่งมอบหลัก/);
assert.match(slide5, /Source Code และฐานข้อมูล/);
assert.match(slide5, /คู่มือผู้ใช้งาน/);
assert.match(slide5, /ผลการทดสอบ UAT/);
assert.match(slide5, /การติดตั้ง อบรม และดูแลหลังขึ้นระบบ/);
```

Extend the facilitator-documents test:

```js
assert.match(runbook, /เมื่อดำเนินงานครบตามแผน สิ่งส่งมอบหลักจะประกอบด้วย/);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `npm run test:js`

Expected: FAIL because slide 5 does not yet contain `สิ่งส่งมอบหลัก` and the runbook does not yet contain the approved paragraph.

- [ ] **Step 3: Add the runbook paragraph**

Insert this paragraph after the first timeline paragraph and before the paragraph about confirming the start date:

```markdown
>
> เมื่อดำเนินงานครบตามแผน สิ่งส่งมอบหลักจะประกอบด้วย Source Code และฐานข้อมูล คู่มือสำหรับผู้ใช้งานแต่ละบทบาท ผลการทดสอบ UAT รวมถึงการติดตั้ง การอบรม และการดูแลหลังขึ้นระบบครับ
```

- [ ] **Step 4: Confirm PowerPoint is closed and create a safety copy**

Run:

```powershell
Get-ChildItem -LiteralPath docs/kickoff -Force | Where-Object Name -Like '~$*kickoff*.pptx'
```

Expected: no output. If a lock exists, wait for the user to close PowerPoint.

Copy the PPTX to a timestamped file under `$env:TEMP` before editing.

- [ ] **Step 5: Add the slide 5 summary bar with PowerPoint COM**

Open the presentation read-write with no window, confirm it has five slides, and add only these shapes to slide 5:

- rounded rectangle background: left `40`, top `365`, width `880`, height `70` points; fill `E6F3F3`; line `B9DADA`;
- title textbox: left `62`, top `383`, width `130`, height `24`; Tahoma 15 pt bold, color `2A7F80`;
- items textbox: left `195`, top `378`, width `700`, height `34`; Tahoma 12 pt, color `486174`.

Use the exact approved title and item text, save, close the presentation, and quit PowerPoint in a `finally` block.

- [ ] **Step 6: Run tests to verify green**

Run: `npm run test:js`

Expected: 7 tests pass, 0 fail.

- [ ] **Step 7: Export and visually inspect slide 5**

Export all five slides to temporary PNG files at 1920×1080 and inspect slide 5 at original detail. Confirm the summary bar does not overlap the timeline or footer, the items stay readable, and the deck remains five slides.

- [ ] **Step 8: Preserve the user's runbook edit and commit only intended changes**

Temporarily restore the opening line to its `HEAD` wording, stage the runbook, and immediately reapply the user's wording in the worktree. Verify `git diff --cached` does not include the opening line, then stage the PPTX and test:

```powershell
git add -- docs/kickoff/2026-07-15-facilitator-runbook.md docs/kickoff/2026-07-15-kickoff-system-preview.pptx tests/js/kickoff-package.test.mjs
git diff --cached --check
git commit -m "docs: add timeline deliverables summary"
```

- [ ] **Step 9: Final verification**

Run:

```powershell
npm run test:js
git diff --check
git status --short
```

Expected: all tests pass; only the user's pre-existing runbook opening-line edit and unrelated untracked UAT files remain.
