        // Clean up all existing Summernote instances
        function cleanupSummernote() {
            $('.richtext-editor').each(function() {
                if ($(this).summernote && typeof $(this).summernote === 'function') {
                    try {
                        // Check if summernote is initialized
                        if ($(this).next('.note-editor').length > 0) {
                            $(this).summernote('destroy');
                        }
                    } catch (e) {
                        console.error('Error destroying summernote:', e);
                    }
                }
                
                // Remove any leftover Summernote DOM elements
                $(this).next('.note-editor').remove();
                
                // Reset any Summernote classes and attributes
                $(this).removeClass('note-editor note-frame note-editable');
                $(this).removeData('summernoteInitialized');
                $(this).removeAttr('style');
                $(this).show(); // Make sure textarea is visible
            });
        }

        function resetSummernoteClone(node) {
            $(node).find('.richtext-editor').each(function() {
                const $editor = $(this);

                if (typeof $editor.summernote === 'function' && $editor.next('.note-editor').length > 0) {
                    $editor.summernote('destroy');
                }

                $editor.next('.note-editor').remove();
                $editor.removeData('summernoteInitialized');
                $editor.removeClass('note-editor note-frame note-editable note-airframe');
                $editor.removeAttr('style').show().val('');
            });
        }

        // Initialize Summernote when document is ready
        $(document).ready(function() {
            // Don't initialize here - let it be handled by populateForm after data is loaded
        });

        function buildSummernoteOptions($editor) {
            let placeholder = 'กรุณาใส่คำอธิบายเพิ่มเติม...';

            if ($editor.hasClass('qual_sub_description') || $editor.attr('name')?.includes('qual_sub_description')) {
                placeholder = 'ใส่คำอธิบายการให้คะแนน';
            }

            return {
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder: placeholder,
                lang: 'th-TH',
                callbacks: {
                    onChange: function(contents) {
                        $editor.val(contents);
                        markDirty();
                    }
                }
            };
        }

        function initializeSummernote(container = null) {
            const targetSelector = container ? $(container).find('.richtext-editor') : $('.richtext-editor');

            targetSelector.each(function() {
                const $editor = $(this);

                if ($editor.next('.note-editor').length > 0 || $editor.data('summernoteInitialized') === true) {
                    return;
                }

                try {
                    $editor.summernote(buildSummernoteOptions($editor));
                    $editor.data('summernoteInitialized', true);
                } catch (e) {
                    console.error('Error initializing summernote:', e);
                }
            });
        }

        function setupLazySummernote() {
            document.addEventListener('focusin', function (event) {
                const editor = event.target.closest('.richtext-editor');
                if (!editor) {
                    return;
                }

                initializeSummernote(editor.parentElement || editor);
            });
        }

        function observeVisibleSummernote() {
            const editors = document.querySelectorAll('.richtext-editor');

            if (!('IntersectionObserver' in window)) {
                editors.forEach((editor) => initializeSummernote(editor.parentElement || editor));
                return;
            }

            if (!window.richtextObserver) {
                window.richtextObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        initializeSummernote(entry.target.parentElement || entry.target);
                        window.richtextObserver.unobserve(entry.target);
                    });
                }, {
                    root: null,
                    rootMargin: '200px 0px',
                    threshold: 0.01,
                });
            }

            editors.forEach((editor) => {
                if ($(editor).data('summernoteInitialized') === true || $(editor).next('.note-editor').length > 0) {
                    return;
                }

                window.richtextObserver.observe(editor);
            });
        }
