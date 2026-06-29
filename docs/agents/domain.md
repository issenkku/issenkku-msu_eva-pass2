# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the codebase.

## Layout

This is a single-context repo.

Read these when present:

- `CONTEXT.md` at the repo root
- `docs/adr/` for architectural decisions that touch the area being changed

If these files do not exist, proceed silently. Producer skills can create them lazily when domain terms or decisions get resolved.

## Use the glossary's vocabulary

When output names a domain concept, use the term as defined in `CONTEXT.md`. Do not drift to synonyms the glossary explicitly avoids.

If the concept needed is not in the glossary yet, note the gap and resolve it through the relevant domain-doc workflow before making the term load-bearing.

## Flag ADR conflicts

If output contradicts an existing ADR, surface it explicitly rather than silently overriding.

## Admin Dashboard Query Pipeline

The admin dashboard is a read model assembled from reports, assignments, users, departments, positions, status groups, graph data, and score summaries.

Keep `App\Support\AdminDashboardQuery` thin. Put status grouping in `App\Support\AdminDashboardStatusSummary`.

The quantity-score lookup is now batched. The remaining hotspot is quality-score assembly, which still loops per report through `ScoreService::calculateQualityScoreRaw()`.
