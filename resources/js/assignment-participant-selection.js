export function mergeVisibleSelections(currentSelected, visibleValues, checkedVisibleValues) {
    const selectedValues = new Set(currentSelected.map(String));

    visibleValues.forEach((value) => selectedValues.delete(String(value)));
    checkedVisibleValues.forEach((value) => selectedValues.add(String(value)));

    return Array.from(selectedValues);
}

if (typeof window !== 'undefined') {
    window.AssignmentParticipantSelection = { mergeVisibleSelections };
}
