{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/script-edit-data-helpers.blade.php --}}
        function dedupeByKey(items, keyFn) {
            const map = new Map();
            (items || []).forEach(item => {
                const key = keyFn(item);
                if (!map.has(key)) {
                    map.set(key, item);
                }
            });
            return Array.from(map.values());
        }

        function getRichTextValue(element) {
            const $element = $(element);

            if ($element.next('.note-editor').length > 0 && typeof $element.summernote === 'function') {
                return ($element.summernote('code') || '').trim();
            }

            return ($element.val() || '').trim();
        }
