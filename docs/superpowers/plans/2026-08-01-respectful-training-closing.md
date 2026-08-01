# Respectful Training Closing Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the closing speaker note in both facilitator decks with the approved respectful wording.

**Architecture:** Update the final entry in each deck's `SCRIPTS` array in the existing JavaScript deck builder, regenerate both PowerPoint files, then inspect speaker notes and rendered slides. The evidence-link copy already approved will be included in the same regeneration.

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, Microsoft PowerPoint rendering, PowerShell OOXML checks

## Global Constraints

- Use the exact approved closing copy in both decks.
- Remove `ขอให้จำไว้` from the closing speaker notes.
- Preserve 18 slides and 18 speaker-note sections per deck.
- Preserve the presentation-first, demonstration-afterward sequence.

---

### Task 1: Update the speaker-note source

**Files:**
- Modify: `C:/Users/pisut/AppData/Local/Temp/codex-presentations/training-facilitator-2026-07-30/tmp/build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: `SCRIPTS.academic[17]` and `SCRIPTS.support[17]`
- Produces: identical approved closing copy in both final-slide speaker notes

- [ ] **Step 1: Replace the academic closing sentence with the approved copy**
- [ ] **Step 2: Replace the support closing sentence with the approved copy**
- [ ] **Step 3: Search the builder and confirm the old closing phrase is absent**

### Task 2: Regenerate and verify both decks

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: updated deck builder
- Produces: two final PowerPoint files with revised visible evidence copy and closing notes

- [ ] **Step 1: Run the existing JavaScript builder and confirm exit code 0**
- [ ] **Step 2: Inspect OOXML for 18 slides, 18 notes, approved closing copy, evidence copy, test link, and required note markers**
- [ ] **Step 3: Render all slides and visually inspect the changed slides at full size**
- [ ] **Step 4: Run overflow and PowerPoint object-position checks**
- [ ] **Step 5: Commit only the two final PowerPoint files**
