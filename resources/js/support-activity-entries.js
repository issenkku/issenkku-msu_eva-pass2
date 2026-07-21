export function activityEntryFieldName(criterionId, index, field) {
    return `support_list[${criterionId}][activity_entries][${index}][${field}]`;
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

if (typeof window !== 'undefined') {
    window.SupportActivityEntries = {
        activityEntryFieldName,
        activityEntryIdsKeepOriginalOrder,
        activityHtmlHasVisibleText,
        activityHtmlPlainText,
    };
}
