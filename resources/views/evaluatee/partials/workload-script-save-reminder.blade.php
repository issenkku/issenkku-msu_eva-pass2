<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stateEl = document.getElementById('workloadSaveState');
        const reminderEl = document.getElementById('workloadSaveReminder');
        const reminderTextEl = document.getElementById('workloadSaveReminderText');
        const workloadScoreForm = document.getElementById('workloadScoreForm');
        const backLinks = document.querySelectorAll('.workload-back-btn');

        if (!stateEl || !reminderEl) {
            return;
        }

        const parseScore = function (value) {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        const currentTotal = parseScore(stateEl.dataset.currentTotal || '0');
        const savedTotalRaw = stateEl.dataset.savedTotal || '';
        const hasSavedTotal = savedTotalRaw !== '';
        const savedTotal = hasSavedTotal ? parseScore(savedTotalRaw) : 0;
        let hasUnsavedChanges = hasSavedTotal
            ? Math.abs(currentTotal - savedTotal) > 0.0001
            : currentTotal > 0;
        let allowPageExit = false;

        // แสดงแถบเตือนเมื่อคะแนนภาระงานล่าสุดยังไม่ได้กดบันทึก
        function updateReminder() {
            reminderEl.hidden = !hasUnsavedChanges;
            if (!hasUnsavedChanges || !reminderTextEl) {
                return;
            }

            reminderTextEl.textContent = 'กรุณากดบันทึกด้านล่างเพื่อยืนยันคะแนนภาระงานล่าสุด';
        }

        if (workloadScoreForm) {
            workloadScoreForm.addEventListener('submit', function () {
                allowPageExit = true;
                hasUnsavedChanges = false;
                updateReminder();
            });
        }

        document.addEventListener('submit', function () {
            allowPageExit = true;
        }, true);

        backLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (!hasUnsavedChanges) {
                    return;
                }

                const confirmed = window.confirm('มีข้อมูลภาระงานที่ยังไม่ได้บันทึก ต้องการย้อนกลับหรือไม่?');
                if (!confirmed) {
                    event.preventDefault();
                    return;
                }

                allowPageExit = true;
            });
        });

        window.addEventListener('beforeunload', function (event) {
            if (!hasUnsavedChanges || allowPageExit) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });

        updateReminder();
    });
</script>
