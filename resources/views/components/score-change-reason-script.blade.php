<script>
    (() => {
        const normalizeScoreValue = (value) => {
            const trimmed = String(value ?? '').trim();
            if (trimmed === '') return '';

            const number = Number(trimmed);
            return Number.isFinite(number) ? number.toFixed(2) : trimmed;
        };

        const currentValueFor = (reason) => {
            const inputName = reason.dataset.scoreInputName;
            if (!inputName) return '';

            return document.getElementsByName(inputName)[0]?.value ?? '';
        };

        const updateReasonVisibility = (reason) => {
            const changed = normalizeScoreValue(currentValueFor(reason))
                !== normalizeScoreValue(reason.dataset.originalValue);
            const container = reason.closest('[data-score-change-reason-container]');

            container?.classList.toggle('hidden', !changed);
            reason.required = changed;
            if (!changed) reason.removeAttribute('aria-invalid');

            return changed;
        };

        window.validateScoreChangeReasons = () => {
            const errors = [];
            let firstInvalid = null;

            document.querySelectorAll('[data-score-change-reason]').forEach((reason) => {
                if (!updateReasonVisibility(reason) || reason.value.trim() !== '') return;

                reason.setAttribute('aria-invalid', 'true');
                firstInvalid ??= reason;
                errors.push('กรุณาระบุเหตุผลที่แก้ไขคะแนน');
            });

            if (firstInvalid) {
                let parent = firstInvalid.parentElement;
                while (parent) {
                    if (parent.tagName === 'DETAILS') parent.open = true;
                    parent = parent.parentElement;
                }
                firstInvalid.focus();
            }

            return errors;
        };

        const refreshChangedReasons = () => {
            document.querySelectorAll('[data-score-change-reason]').forEach((reason) => {
                updateReasonVisibility(reason);
            });
        };

        document.addEventListener('input', refreshChangedReasons);
        document.addEventListener('change', refreshChangedReasons);
    })();
</script>
