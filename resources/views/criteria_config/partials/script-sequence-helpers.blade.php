{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/script-sequence-helpers.blade.php --}}
        function updateButtonStates(containerSelector, upBtnSelector, downBtnSelector) {
            const items = document.querySelectorAll(containerSelector);
            items.forEach((item, index) => {
                const upBtn = item.querySelector(upBtnSelector);
                const downBtn = item.querySelector(downBtnSelector);
                if (upBtn) upBtn.disabled = index === 0;
                if (downBtn) downBtn.disabled = index === items.length - 1;
            });
        }

        function bindSequenceInputs() {}

        function setSequenceInputValue(input, value) {
            if (!input) {
                return;
            }

            input.textContent = String(value);
        }

        function getSequenceValue(block, selector, fallback) {
            const input = block.querySelector(selector);
            return (input?.textContent || '').trim() || String(fallback);
        }

        function parseSequenceValue(value) {
            return String(value || '')
                .split('.')
                .map((part) => {
                    const parsed = Number(part);
                    return Number.isFinite(parsed) ? parsed : Number.MAX_SAFE_INTEGER;
                });
        }

        function compareSequenceValues(left, right) {
            const leftParts = parseSequenceValue(left);
            const rightParts = parseSequenceValue(right);
            const maxLength = Math.max(leftParts.length, rightParts.length);

            for (let index = 0; index < maxLength; index++) {
                const leftPart = leftParts[index] ?? -1;
                const rightPart = rightParts[index] ?? -1;
                if (leftPart !== rightPart) {
                    return leftPart - rightPart;
                }
            }

            return String(left).localeCompare(String(right), undefined, { numeric: true });
        }
