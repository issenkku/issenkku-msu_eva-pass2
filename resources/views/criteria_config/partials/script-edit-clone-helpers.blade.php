        // Helper สำหรับล้าง identity เดิมและ clone block ใหม่
        function clearIdentityAttributes(rootElement) {
            const identityAttrs = [
                'data-category-id',
                'data-evaluation-id',
                'data-quantity-main-id',
                'data-quality-main-id',
                'data-quality-sub-id',
            ];

            identityAttrs.forEach(attr => rootElement.removeAttribute(attr));
            rootElement.querySelectorAll(identityAttrs.map(attr => `[${attr}]`).join(',')).forEach(el => {
                identityAttrs.forEach(attr => el.removeAttribute(attr));
            });

            rootElement.querySelectorAll('.category_id_value, .evaluation_id_value, .quantity_main_id_value, .quality_main_id_value, .quant_sub_criteria_id').forEach(el => {
                el.value = '';
            });
        }

        function cloneAndClear(blockSelector) {
            let node = document.querySelector(blockSelector).cloneNode(true);
            clearIdentityAttributes(node);

            $(node).find('.richtext-editor').each(function() {
                const $editor = $(this);

                $editor.next('.note-editor').remove();

                const newId = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.id = newId;
                this.value = '';

                $editor.removeClass('note-editor note-frame note-editable');
                $editor.removeAttr('style');
                $editor.show();
            });

            node.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
            node.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
            node.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
            node.querySelectorAll('textarea.quant_formula').forEach(textarea => textarea.value = 'D = A × C / B');
            node.querySelectorAll(
                '.evaluation_list_block:not(:first-child), .quant_criteria_block:not(:first-child), .qual_criteria_block:not(:first-child), .quant_sub_criteria_block:not(:first-child), .qual_sub_criteria_block:not(:first-child)'
            ).forEach(e => e.remove());

            if (blockSelector === '.evaluation_list_block') {
                const container = node.closest('.category_block').querySelector('.evaluation_lists_container');
                const index = container.querySelectorAll('.evaluation_list_block').length + 1;
                setSequenceInputValue(node.querySelector('.eval_sequence'), index);
                node.querySelector('.quantity_main_criterias_container').classList.add('hidden');
                node.querySelector('.quality_main_criterias_container').classList.add('hidden');
            }

            if (blockSelector === '.category_block') {
                const container = document.getElementById('categories_container');
                const index = container.querySelectorAll('.category_block').length + 1;
                setSequenceInputValue(node.querySelector('.category_sequence'), index);
            }

            return node;
        }
