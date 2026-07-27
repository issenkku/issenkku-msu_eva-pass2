export function resolveSupportIndicatorMode({ allowActivities, allowEvaluateeIndicator, grouped }) {
    if (!allowActivities) {
        return {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: false,
            groupedDisabled: true,
        };
    }

    const evaluateeOwnsIndicator = Boolean(allowEvaluateeIndicator);
    const groupedMode = Boolean(grouped) && !evaluateeOwnsIndicator;

    return {
        allowEvaluateeIndicatorChecked: evaluateeOwnsIndicator,
        allowEvaluateeIndicatorDisabled: groupedMode,
        groupedChecked: groupedMode,
        groupedDisabled: evaluateeOwnsIndicator,
    };
}

if (typeof window !== 'undefined') {
    window.SupportIndicatorMode = { resolveSupportIndicatorMode };
}
