export function activityEntryFieldName(criterionId, index, field) {
    return `support_list[${criterionId}][activity_entries][${index}][${field}]`;
}

export function activityEvidenceFieldName(criterionId, index) {
    return `support_list[${criterionId}][activity_entries][${index}][evidence_links][]`;
}

export function activityEvidenceGroups(entries) {
    return entries
        .map((entry, index) => ({
            id: entry.id ?? `new-${index}`,
            label: `รายการ ${index + 1}`,
            links: (entry.evidence_links ?? []).filter(Boolean),
        }))
        .filter((group) => group.links.length > 0);
}

export function activityHtmlPlainText(html) {
    return String(html ?? '')
        .replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, '')
        .replace(/<br\s*\/?>/gi, ' ')
        .replace(/<[^>]*>/g, ' ')
        .replace(/&(nbsp|#160|#xA0);/gi, ' ')
        .replace(/&amp;/gi, '&')
        .replace(/&lt;/gi, '<')
        .replace(/&gt;/gi, '>')
        .replace(/&quot;/gi, '"')
        .replace(/&#0*39;/gi, "'")
        .replace(/\s+/gu, ' ')
        .trim();
}

export function activityHtmlHasVisibleText(html) {
    return activityHtmlPlainText(html) !== '';
}

export function activityEntryIdsKeepOriginalOrder(submittedIds, originalIds) {
    return submittedIds.length === originalIds.length
        && submittedIds.every((id, index) => id === originalIds[index]);
}

export function groupActivityEntries(indicatorItems, entries) {
    return indicatorItems.map((item) => ({
        ...item,
        activity_entries: entries.filter(
            (entry) => Number(entry.support_indicator_item_id) === Number(item.id),
        ),
    }));
}

if (typeof window !== 'undefined') {
    window.SupportActivityEntries = {
        activityEvidenceFieldName,
        activityEvidenceGroups,
        activityEntryFieldName,
        activityEntryIdsKeepOriginalOrder,
        activityHtmlHasVisibleText,
        activityHtmlPlainText,
        groupActivityEntries,
    };
}
