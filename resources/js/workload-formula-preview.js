const builtInLabels = Object.freeze({
    item_star: 'ค่าภารงาน',
    credits: 'หน่วยกิตรวม',
    lecture_credits: 'หน่วยกิตบรรยาย',
    lab_credits: 'หน่วยกิตปฏิบัติ',
    self_study_credits: 'หน่วยกิตศึกษาด้วยตนเอง',
});

export function formatWorkloadFormulaPreview(formula, customLabels = {}) {
    const source = String(formula ?? '');
    if (source.trim() === '') {
        return 'พรีวิวจะแสดงเมื่อระบุสูตรการคำนวณ';
    }

    const labels = Object.fromEntries(
        Object.entries({ ...builtInLabels, ...customLabels }).map(([name, label]) => [
            name.toLowerCase(),
            String(label),
        ]),
    );

    return source
        .replace(/[A-Za-z_][A-Za-z0-9_]*/g, (identifier) => labels[identifier.toLowerCase()] ?? identifier)
        .replaceAll('*', '×')
        .replaceAll('/', '÷');
}

if (typeof window !== 'undefined') {
    window.WorkloadFormulaPreview = {
        format: formatWorkloadFormulaPreview,
    };
}
