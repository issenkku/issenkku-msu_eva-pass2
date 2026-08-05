export async function requestWorkloadEntrySave(form, fetchImpl = window.fetch.bind(window)) {
    const response = await fetchImpl(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(payload.message || 'Unable to save workload entry');
        error.status = response.status;
        error.errors = payload.errors || {};
        throw error;
    }
    return payload;
}

function createTotalUpdatedEvent(documentRef, total) {
    const CustomEventConstructor = documentRef.defaultView?.CustomEvent ?? globalThis.CustomEvent;

    if (typeof CustomEventConstructor === 'function') {
        return new CustomEventConstructor('workload:total-updated', {
            detail: { total },
        });
    }

    if (typeof documentRef.createEvent === 'function') {
        const event = documentRef.createEvent('CustomEvent');
        event.initCustomEvent('workload:total-updated', false, false, { total });
        return event;
    }

    return { type: 'workload:total-updated', detail: { total } };
}

export function applyWorkloadEntrySaveResponse(documentRef, payload) {
    const panelsRegion = documentRef.getElementById('workloadPanelsLiveRegion');
    const summaryRegion = documentRef.getElementById('workloadSummaryLiveRegion');
    const saveState = documentRef.getElementById('workloadSaveState');
    const total = Number(payload.total_score ?? 0);

    if (panelsRegion) {
        panelsRegion.innerHTML = payload.panels_html ?? '';
    }
    if (summaryRegion) {
        summaryRegion.innerHTML = payload.summary_html ?? '';
    }
    if (saveState) {
        saveState.dataset.currentTotal = String(payload.total_score ?? 0);
    }

    documentRef.dispatchEvent(createTotalUpdatedEvent(documentRef, total));
}

if (typeof window !== 'undefined') {
    window.WorkloadEntrySubmit = {
        requestWorkloadEntrySave,
        applyWorkloadEntrySaveResponse,
    };
}
