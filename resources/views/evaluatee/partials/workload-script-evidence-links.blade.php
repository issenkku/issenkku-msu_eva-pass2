<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('workload-evidence-links');
        const addBtn = document.getElementById('workload-add-evidence-link');

        if (!container || !addBtn) {
            return;
        }

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.workload-evidence-row');
            rows.forEach(function (row) {
                const removeBtn = row.querySelector('.workload-evidence-remove-btn');
                if (removeBtn) {
                    removeBtn.disabled = rows.length === 1;
                }
            });
        }

        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'workload-evidence-row';
            row.innerHTML = `
                <input type="text" class="workload-modal-input" name="evidence_links[]" placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้" />
                <button type="button" class="workload-evidence-remove-btn" title="ลบลิงก์">ลบ</button>
            `;
            container.appendChild(row);
            updateRemoveButtons();
        });

        container.addEventListener('click', function (event) {
            const btn = event.target.closest('.workload-evidence-remove-btn');
            if (!btn) {
                return;
            }

            const rows = container.querySelectorAll('.workload-evidence-row');
            if (rows.length <= 1) {
                return;
            }

            const row = btn.closest('.workload-evidence-row');
            if (row) {
                row.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    });
</script>
