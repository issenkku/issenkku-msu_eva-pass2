        let draggedBlock = null;

        document.addEventListener('pointerdown', function (e) {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });

            const handle = e.target.closest('.drag_handle');
            if (!handle) {
                return;
            }

            const block = handle.closest('[data-draggable-level]');
            if (block) {
                block.dataset.dragArmed = 'true';
            }
        });

        document.addEventListener('pointerup', function () {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });
        });

        document.addEventListener('dragstart', function (e) {
            const block = e.target.closest('[data-draggable-level]');
            if (!block || block.dataset.dragArmed !== 'true') {
                e.preventDefault();
                return;
            }

            draggedBlock = block;
            block.classList.add('opacity-60');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', block.dataset.draggableLevel || 'move');
        });

        document.addEventListener('dragover', function (e) {
            if (!draggedBlock) {
                return;
            }

            const target = e.target.closest('[data-draggable-level]');
            if (!target || target === draggedBlock) {
                return;
            }

            if (target.dataset.draggableLevel !== draggedBlock.dataset.draggableLevel || target.parentElement !== draggedBlock.parentElement) {
                return;
            }

            e.preventDefault();
            const rect = target.getBoundingClientRect();
            const insertAfter = (e.clientY - rect.top) > (rect.height / 2);
            target.parentElement.insertBefore(draggedBlock, insertAfter ? target.nextElementSibling : target);
            refreshOrderUI();
        });

        document.addEventListener('dragend', function () {
            if (draggedBlock) {
                draggedBlock.classList.remove('opacity-60');
                delete draggedBlock.dataset.dragArmed;
                markDirty();
            }
            draggedBlock = null;
        });
