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

export function calculateSupportItemWeightedScore({
    criterionWeight,
    criterionScore,
    activityEntries = [],
} = {}) {
    const rawCriterionScore = String(criterionScore ?? '').trim();
    if (rawCriterionScore !== '') {
        const weightedScore = calculateEntryWeightedScore(criterionWeight, rawCriterionScore);

        return {
            weightedScore: weightedScore ?? 0,
            hasData: weightedScore !== null,
        };
    }

    const entryScores = activityEntries
        .map(({ weight, achievedScore }) => calculateEntryWeightedScore(
            String(weight ?? '').trim(),
            String(achievedScore ?? '').trim(),
        ))
        .filter((value) => value !== null);

    return {
        weightedScore: Math.round(entryScores.reduce((total, value) => total + value, 0) * 100) / 100,
        hasData: entryScores.length > 0,
    };
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

export function getSupportScoreValidationState(value, targetValue, { required = false } = {}) {
    const rawValue = String(value ?? '').trim();
    const valid =
        (!required && rawValue === '') ||
        (rawValue !== '' && isCriterionScoreValid(rawValue, targetValue));

    return {
        valid,
        message: valid
            ? ''
            : `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย ${targetValue}`,
        helpVisible: valid,
        errorVisible: !valid,
    };
}

if (typeof window !== 'undefined') {
    window.SupportScoreCalculator = {
        calculateEntryWeightedScore,
        calculateSupportItemWeightedScore,
        calculateSupportAchievement,
        getSupportScoreValidationState,
        isCriterionScoreValid,
    };
}
