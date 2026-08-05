export class AsyncMutationError extends Error {
    constructor(message, { errors = {}, payload = {}, status = 0 } = {}) {
        super(message);
        this.name = 'AsyncMutationError';
        this.errors = errors;
        this.payload = payload;
        this.status = status;
    }
}

function mutationError(payload, status, fallbackMessage) {
    return new AsyncMutationError(payload?.message || fallbackMessage, {
        errors: payload?.errors || {},
        payload: payload || {},
        status,
    });
}

export async function requestFormMutation(form, fetchImpl = globalThis.fetch) {
    let response;

    try {
        response = await fetchImpl(form.action, {
            body: new FormData(form),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            method: String(form.method || 'POST').toUpperCase(),
        });
    } catch (error) {
        throw error;
    }

    let payload = {};
    try {
        payload = await response.json();
    } catch {
        throw mutationError({}, response.status, 'เซิร์ฟเวอร์ส่งข้อมูลตอบกลับไม่ถูกต้อง');
    }

    if (response.redirected) {
        throw mutationError(payload, response.status, 'ไม่สามารถอัปเดตหน้าโดยไม่รีเฟรชได้');
    }

    if (!response.ok) {
        throw mutationError(payload, response.status, 'ไม่สามารถดำเนินการได้');
    }

    if (payload?.success !== true) {
        throw mutationError(payload, response.status, 'ข้อมูลตอบกลับไม่ครบถ้วน');
    }

    return payload;
}

export function createAsyncFormCoordinator({
    applySuccess,
    applyValidationErrors = () => {},
    form,
    getSubmitButton = () => form?.querySelector?.('[type="submit"]') || null,
    request = requestFormMutation,
    savingLabel = 'กำลังบันทึก...',
    showMessage = () => {},
}) {
    let inFlight = false;

    return async function coordinateAsyncForm(event) {
        event?.preventDefault?.();
        if (inFlight) return;

        inFlight = true;
        const button = getSubmitButton();
        const originalLabel = button?.textContent;

        if (button) {
            button.disabled = true;
            if (savingLabel) button.textContent = savingLabel;
        }

        try {
            const payload = await request(form);
            await applySuccess(payload);
            showMessage(payload.message || 'ดำเนินการเรียบร้อยแล้ว', false);
        } catch (error) {
            if (error?.status === 422) {
                applyValidationErrors(error.errors || {});
            }
            showMessage(error?.message || 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง', true);
        } finally {
            inFlight = false;
            if (button) {
                button.disabled = false;
                button.textContent = originalLabel;
            }
        }
    };
}

const browserApi = {
    AsyncMutationError,
    createAsyncFormCoordinator,
    requestFormMutation,
};

if (typeof window !== 'undefined') {
    window.AsyncForm = browserApi;
}

export default browserApi;
