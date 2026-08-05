function resourceSelector(id) {
    const escaped = globalThis.CSS?.escape ? globalThis.CSS.escape(String(id)) : String(id).replace(/["\\]/g, '\\$&');
    return `[data-resource-row][data-resource-id="${escaped}"]`;
}

function elementFromHtml(documentRef, html) {
    const template = documentRef.createElement('template');
    template.innerHTML = String(html).trim();
    return template.content.firstElementChild;
}

export function applyResourceMutation(documentRef, payload, options = {}) {
    const state = payload?.state || {};
    const deletedIds = Array.isArray(state.deleted_ids) ? state.deleted_ids : [];

    deletedIds.forEach((id) => {
        documentRef.querySelector(resourceSelector(id))?.remove();
    });

    if (!payload?.html?.row) return;
    if (state.id === undefined || state.id === null || state.id === '') {
        throw new Error('A resource id is required before applying a row fragment');
    }

    const replacement = elementFromHtml(documentRef, payload.html.row);
    if (!replacement) throw new Error('The row fragment is empty');

    const current = documentRef.querySelector(resourceSelector(state.id));
    if (current) {
        const currentSequence = current.querySelector?.('[data-sequence]')?.textContent;
        const replacementSequence = replacement.querySelector?.('[data-sequence]');
        if (replacementSequence && currentSequence !== undefined) {
            replacementSequence.textContent = currentSequence;
        }
        current.replaceWith(replacement);
        return;
    }

    const rowsSelector = options.rowsSelector || '[data-resource-rows]';
    const container = documentRef.querySelector(rowsSelector);
    if (!container) throw new Error(`Unable to find resource row container: ${rowsSelector}`);
    container.append(replacement);
}

export async function refreshTableRegion(url, selector, fetchImpl = globalThis.fetch, documentRef = globalThis.document) {
    const response = await fetchImpl(url, {
        headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`Unable to refresh table region (${response.status})`);
    }

    const html = await response.text();
    const parsed = new DOMParser().parseFromString(html, 'text/html');
    const current = documentRef.querySelector(selector);
    const replacement = parsed.querySelector(selector);

    if (!current || !replacement) {
        throw new Error(`Unable to find table region: ${selector}`);
    }

    current.replaceWith(replacement);
}

const browserApi = {
    applyResourceMutation,
    refreshTableRegion,
};

if (typeof window !== 'undefined') {
    window.AsyncResourceTable = browserApi;
}

export default browserApi;
