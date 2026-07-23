export function calculateSupportAchievement(weightedTotal, targetLevelCount) {
    const total = Number(weightedTotal);
    const levels = Number(targetLevelCount);
    if (!Number.isFinite(total) || !Number.isFinite(levels) || levels <= 0) return 0;
    return Math.round((total / levels + Number.EPSILON) * 100) / 100;
}

if (typeof window !== 'undefined') {
    window.SupportScoreCalculator = { calculateSupportAchievement };
}
