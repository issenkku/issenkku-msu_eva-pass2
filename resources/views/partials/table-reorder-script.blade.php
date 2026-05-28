<script>
    (function() {
        function initReorderTable(container) {
            if (!container) {
                return;
            }

            const tbody = container.querySelector('[data-reorder-body]');
            const status = container.querySelector('[data-reorder-status]');
            const reorderUrl = container.dataset.reorderUrl;
            const canReorder = container.dataset.canReorder === '1';
            const startOrder = Number(container.dataset.startOrder || '1');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!tbody || !reorderUrl) {
                return;
            }

            const setStatus = (message, isError = false) => {
                if (!status) {
                    return;
                }

                status.textContent = message;
                status.classList.toggle('text-danger', isError);
                status.classList.toggle('text-muted', !isError);
            };

            const updateSequenceLabels = () => {
                Array.from(tbody.querySelectorAll('tr')).forEach((row, index) => {
                    const sequenceCell = row.querySelector('[data-sequence]');
                    if (sequenceCell) {
                        sequenceCell.textContent = startOrder + index;
                    }
                });
            };

            if (!canReorder) {
                setStatus('ลากจัดอันดับได้เมื่อเลือกโหมดจัดอันดับเอง และไม่ได้ค้นหาหรือกรองข้อมูล');
                return;
            }

            let draggedRow = null;
            let isSaving = false;
            let dragStartOrder = '';

            const moveRow = (row, direction) => {
                if (!row) {
                    return false;
                }

                const targetRow = direction === 'up'
                    ? row.previousElementSibling
                    : row.nextElementSibling;

                if (!targetRow) {
                    return false;
                }

                if (direction === 'up') {
                    tbody.insertBefore(row, targetRow);
                } else {
                    tbody.insertBefore(targetRow, row);
                }

                updateSequenceLabels();
                return true;
            };

            const persistOrder = async () => {
                if (isSaving) {
                    return;
                }

                isSaving = true;
                setStatus('กำลังบันทึกลำดับ...');

                try {
                    const ids = Array.from(tbody.querySelectorAll('tr')).map((row) => Number(row.dataset.id));

                    const response = await fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ?? '',
                        },
                        body: JSON.stringify({
                            ids,
                            start_order: startOrder,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    updateSequenceLabels();
                    setStatus('บันทึกลำดับแล้ว');
                } catch (error) {
                    setStatus('บันทึกลำดับไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', true);
                } finally {
                    isSaving = false;
                }
            };

            Array.from(tbody.querySelectorAll('tr')).forEach((row) => {
                const handle = row.querySelector('[data-drag-handle]');

                row.draggable = false;

                if (handle) {
                    handle.draggable = true;
                }

                const startDraggingRow = (event) => {
                    dragStartOrder = Array.from(tbody.querySelectorAll('tr'))
                        .map((item) => item.dataset.id)
                        .join(',');
                    draggedRow = row;
                    row.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', row.dataset.id);
                };

                if (handle) {
                    handle.addEventListener('dragstart', startDraggingRow);
                    handle.addEventListener('dragend', async () => {
                        row.classList.remove('is-dragging');
                        tbody.querySelectorAll('.is-drag-over').forEach((item) => item.classList.remove('is-drag-over'));
                        const currentOrder = Array.from(tbody.querySelectorAll('tr'))
                            .map((item) => item.dataset.id)
                            .join(',');
                        draggedRow = null;

                        if (dragStartOrder && dragStartOrder !== currentOrder) {
                            await persistOrder();
                        }
                    });
                }

                row.addEventListener('dragend', () => {
                    row.classList.remove('is-dragging');
                    tbody.querySelectorAll('.is-drag-over').forEach((item) => item.classList.remove('is-drag-over'));
                    draggedRow = null;
                });

                row.addEventListener('dragover', (event) => {
                    if (!draggedRow || draggedRow === row) {
                        return;
                    }

                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';

                    const rect = row.getBoundingClientRect();
                    const shouldInsertAfter = (event.clientY - rect.top) > (rect.height / 2);

                    row.classList.add('is-drag-over');

                    if (shouldInsertAfter) {
                        row.after(draggedRow);
                    } else {
                        row.before(draggedRow);
                    }
                });

                row.addEventListener('dragleave', () => {
                    row.classList.remove('is-drag-over');
                });

                row.addEventListener('drop', (event) => {
                    event.preventDefault();
                    row.classList.remove('is-drag-over');
                });
            });

            tbody.addEventListener('click', async (event) => {
                const moveButton = event.target.closest('[data-move-direction]');

                if (!moveButton || isSaving) {
                    return;
                }

                const row = moveButton.closest('tr');
                const direction = moveButton.dataset.moveDirection;

                if (!row || !direction) {
                    return;
                }

                event.preventDefault();

                if (moveRow(row, direction)) {
                    await persistOrder();
                }
            });

            updateSequenceLabels();
            setStatus('ลากแถวเพื่อจัดอันดับ');
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-reorder-table]').forEach(initReorderTable);
        });
    })();
</script>
