# Admin Dashboard Query Pipeline Notes

## Remaining Risk

`AdminDashboardQuery::reportsWithScores()` no longer does one `QuantityScore` sum per report, but the quality-score path is still per-report:

- `ScoreService::calculateAverageScore()` calls `calculateQualityScoreRaw($reportId)` inside a loop.
- `AdminDashboardQuery::reportsWithScores()` also calls `ScoreService::calculateQualityScoreRaw($report->report_id)` inside a loop.

That means the next meaningful performance pass is to batch quality-score retrieval by `report_id` and return a map instead of recomputing per report.

Do not rewrite this until a fixture or factory-backed test can pin the expected totals for multiple completed reports.
