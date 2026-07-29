export function calculateSupportAchievement(weightedTotal, targetLevelCount) {
    const total = Number(weightedTotal);
    const levels = Number(targetLevelCount);
    if (!Number.isFinite(total) || !Number.isFinite(levels) || levels <= 0) return 0;
    return Math.round((total / levels + Number.EPSILON) * 100) / 100;
}

export function calculateEntryWeightedScore(weight, achievedScore) {
    if (weight === '' || achievedScore === '') return null;
    const result = (Number(weight) * Number(achievedScore)) / 100;

    return Number.isFinite(result) ? Math.round(result * 100) / 100 : null;
}

export function isCriterionScoreValid(value, targetValue) {
    const rawValue = String(value ?? '').trim();
    if (rawValue === '') return true;

    const score = Number(rawValue);
    const target = Number(targetValue);

    return (
        Number.isInteger(score) &&
        score >= 1 &&
        score <= 5 &&
        Number.isFinite(target) &&
        score <= target
    );
}

if (typeof window !== 'undefined') {
    window.SupportScoreCalculator = {
        calculateEntryWeightedScore,
        calculateSupportAchievement,
        isCriterionScoreValid,
    };
}
