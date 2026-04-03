        // Category handlers
        function handleDeleteCategory(categoryBlock) {
            const container = document.getElementById('categories_container');
            if (container.querySelectorAll('.category_block:not([style*="display: none"])').length > 1) {
                if (confirm('ต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
                    categoryBlock.remove();
                    updateSequences();
                    updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
                }
            } else {
                alert('ต้องมีหมวดหมู่การประเมินอย่างน้อย 1 รายการ');
            }
        }

        function handleMoveCategoryUp(categoryBlock) {
            const previous = categoryBlock.previousElementSibling;
            if (previous && previous.classList.contains('category_block')) {
                categoryBlock.parentNode.insertBefore(categoryBlock, previous);
                updateSequences();
                updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            }
        }

        function handleMoveCategoryDown(categoryBlock) {
            const next = categoryBlock.nextElementSibling;
            if (next && next.classList.contains('category_block')) {
                categoryBlock.parentNode.insertBefore(next, categoryBlock);
                updateSequences();
                updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            }
        }

        function handleAddCategory() {
            const newBlock = cloneAndClear('.category_block');
            newBlock.style.display = 'block';
            document.getElementById('categories_container').appendChild(newBlock);
            updateSequences();
            updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
