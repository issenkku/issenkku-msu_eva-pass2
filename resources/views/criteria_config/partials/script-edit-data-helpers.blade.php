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

        function hasVisibleRichText(value) {
            const documentFragment = new DOMParser().parseFromString(value || '', 'text/html');
            const text = (documentFragment.body.textContent || '').replace(/\u00a0/g, ' ');

            return text.trim() !== '';
        }
