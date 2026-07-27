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
    return submittedIds.length === originalIds.length && submittedIds.every((id, index) => id === originalIds[index]);
}

export function groupActivityEntries(indicatorItems, entries) {
    return indicatorItems.map((item) => ({
        ...item,
        activity_entries: entries.filter((entry) => Number(entry.support_indicator_item_id) === Number(item.id)),
    }));
}

export function renderActivityEntryList(target, entries, options = {}) {
    const visibleEntries = entries.filter((entry) => activityHtmlHasVisibleText(entry.html));
    target.replaceChildren();

    if (visibleEntries.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'text-xs font-normal text-slate-400';
        empty.dataset.supportGroupedProjectEmpty = '';
        empty.textContent = options.emptyText ?? 'ยังไม่มีกิจกรรม/โครงการเพิ่มเติม';
        target.appendChild(empty);
        return;
    }

    const list = document.createElement('div');
    list.className = 'space-y-2';
    visibleEntries.forEach((entryData, entryIndex) => {
        const entry = document.createElement('div');
        entry.className = 'flex gap-2';

        const number = document.createElement('span');
        number.className = 'inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800';
        number.dataset.supportGroupedProjectNumber = '';
        number.textContent = String(entryIndex + 1);

        const content = document.createElement('div');
        content.className = 'min-w-0 break-words font-semibold text-amber-800';
        content.textContent = activityHtmlPlainText(entryData.html);

        entry.append(number, content);
        list.appendChild(entry);
    });
    target.appendChild(list);
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
        renderActivityEntryList,
    };
}
