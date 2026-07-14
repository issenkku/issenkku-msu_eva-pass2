# Kickoff + System Preview Design

- **Meeting:** Workload Management Module Phase 2 kickoff
- **Date:** 15 July 2026
- **Facilitator:** Project Team Lead
**Audience:** Faculty executives, instructors, system users, and the development team

## Purpose

Prepare a complete Thai-language meeting package that lets a first-time Team Lead run the kickoff confidently, explain the product features, relate the current system to the TOR, present the contractual delivery plan, and leave the meeting with explicit decisions and owners.

The package must help the facilitator guide the discussion without implying that every TOR item is already complete. Statements about implementation status must be supported by the current application, routes, tests, or an observable demo path.

## Meeting assumptions

- The invitation contains both 08:30 and 08:45. The facilitator should join at 08:15, have the room and demo ready by 08:30, and confirm the formal opening time with the host.
- No end time is stated. The primary agenda is designed for 60 minutes and includes a 30-minute compressed path.
- The meeting is held in Microsoft Teams.
- All stakeholder groups are expected in the same meeting, so the language should remain understandable to non-technical participants.
- Commercial details and payment account information are excluded from the main presentation. They are discussed only if the project owner raises them.

## Deliverables

1. A six-section PowerPoint presentation in Thai for screen sharing.
2. A facilitator runbook with exact opening, transitions, demo narration, recovery phrases, time checks, and closing script.
3. A TOR-to-system demo matrix that identifies what can be shown live, what can be explained with evidence, and what remains planned.
4. A meeting-minutes template for decisions, action items, risks, questions, and parking-lot topics.
5. A demo readiness checklist and a fallback path for application, network, or authentication failures.

All generated artifacts will live under `docs/kickoff/` and will be named with the meeting date `2026-07-15`.

## Presentation structure

### 1. Opening and meeting outcomes — 5 minutes

- Identify the project and participants.
- State that this is a combined kickoff and system preview.
- Set four outcomes: common scope, confirmed plan, required stakeholder decisions, and assigned next actions.

### 2. Problem and project objective — 5 minutes

- Explain the current manual workload-score calculation and subsequent re-entry into the main evaluation system.
- Position Phase 2 as the module for workload capture, automatic calculation, evidence links, and connection to the 2026 main evaluation system.
- Avoid technical implementation details at this stage.

### 3. Features mapped to the TOR — 10 minutes

Present the feature groups in the user's workflow:

- Admin defines workload forms, field types, and scoring rules.
- Evaluatee records workload entries and supporting evidence links.
- The system calculates workload scores and recalculates after edits.
- Roles and permissions control access for Admin, Manager, Director, Evaluator, and Evaluatee.
- Results are prepared for connection to the main evaluation system.
- Auditability, performance, security, manuals, training, and UAT are delivery concerns.

Every feature receives one of three labels: **ready to demonstrate**, **implemented but demonstrated through supporting evidence**, or **planned/awaiting confirmation**.

### 4. Live system preview — 15 minutes

The preferred demo follows one coherent scenario instead of visiting unrelated menus:

1. Sign in as Admin.
2. Open or configure a workload form.
3. Show the formula or scoring-rule representation.
4. Switch to an Evaluatee view.
5. Add or inspect a workload entry and evidence link.
6. Show the resulting calculated score.
7. Show the closest verifiable integration, permission, or audit evidence supported by the current system.

The demo script must never claim that an unverified integration is complete. If a TOR step is unavailable, the facilitator states its current status and where it sits in the delivery plan.

### 5. Contractual 90-day delivery plan — 10 minutes

- Weeks 1–2: analysis and design.
- Weeks 3–8: backend and frontend development.
- Weeks 9–10: system integration and UAT.
- Weeks 11–12: defect correction, deployment, documentation, training, and hypercare.

The meeting must explicitly confirm the timeline baseline because the agreement was signed on 29 June 2026 while the kickoff invitation is dated 15 July 2026.

Required deliverables are summarized as source code and database, Admin manual, Evaluator manual, and Evaluatee manual. UAT acceptance is summarized without overloading the slide: all defined cases executed, critical cases passed, and no critical defect left open, subject to confirmation with the acceptance committee.

### 6. Decisions and next steps — 15 minutes

The facilitator asks for owners and dates for:

- The first approved workload form and required field types.
- The authoritative formulas, rounding rules, missing-data behavior, and formula approver.
- The Phase 1 integration method, identifiers, test environment, and technical contact.
- Test accounts, sample data, and evidence-link access.
- The UAT committee, acceptance authority, and defect-severity definitions.
- The primary communication channel and response expectations.
- Confirmation of the project timeline baseline.

The section closes with a verbal read-back of decisions and action items.

## Visual direction

- Use a restrained university/project style: white or very light background, dark navy headings, muted teal accent, and a single warm accent for risks or pending decisions.
- Use large Thai type suitable for Teams screen sharing and keep each slide focused on one message.
- Prefer a simple workflow and a four-phase timeline over dense bullet lists.
- Do not reproduce personal identity documents, bank details, signatures, or other unnecessary contract imagery.
- Include a small status legend for ready, evidence-only, and planned items.

## Facilitation behavior

- The Team Lead owns the agenda and action log, not every contractual or policy decision.
- Unknown answers are captured as action items with an owner and due date.
- Off-topic issues are placed in a parking lot.
- Conflicting views are restated as explicit alternatives and routed to the named decision owner.
- Time warnings occur at approximately 30, 45, and 55 minutes.

## Failure and fallback handling

- Join Teams at 08:15 and validate screen sharing, audio, application login, test accounts, and browser zoom.
- Keep the required pages and accounts open before the meeting.
- Avoid creating or deleting production data during the preview unless explicitly authorized.
- If authentication or the application fails, continue with prepared screenshots or the TOR-to-system matrix.
- If the network fails, the facilitator continues with the local deck and records a separate demo follow-up.
- If time is reduced to 30 minutes, use 3 minutes for opening, 5 for objectives and scope, 10 for the demo, 5 for the plan, and 7 for decisions and next steps.

## Verification

Before the artifacts are considered ready:

1. Trace every live-demo claim to a route, view, test, or verified application behavior.
2. Reconcile terminology with the TOR: Admin, Manager, Director, Evaluator, and Evaluatee.
3. Check that the 60-minute sections total 60 minutes and the compressed sections total 30 minutes.
4. Verify the deck renders Thai text correctly and remains readable when shared in Teams.
5. Perform a dry run of the preferred demo path without modifying protected data.
6. Check all artifacts for unfinished placeholders and confidential information.

## Success criteria

The package is successful when the Team Lead can open the deck and runbook, lead the meeting without inventing technical answers, demonstrate only verified features, explain the 90-day plan, and finish with a written list of decisions, owners, and dates.
