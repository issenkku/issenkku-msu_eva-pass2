        const unsavedChangesMessage = 'มีข้อมูลที่แก้ไขแล้วยังไม่ได้บันทึก กรุณาบันทึกก่อนออกจากหน้านี้';

        function updateFloatingSaveButton() {
            const floatingButton = document.getElementById('floating_save_button');
            if (!floatingButton) {
                return;
            }

            floatingButton.classList.toggle('hidden', !isDirty || isSubmitting);
        }

        function setDirtyState(nextState) {
            isDirty = Boolean(nextState);
            updateFloatingSaveButton();
        }

        function markDirty() {
            @if (!empty($guardExpression))
            if ({!! $guardExpression !!}) {
                return;
            }
            @else
            if (isSubmitting) {
                return;
            }
            @endif

            setDirtyState(true);
        }

        function resetDirtyState() {
            setDirtyState(false);
        }

        function shouldBlockNavigation(targetUrl = '') {
            if (!isDirty || isSubmitting) {
                return false;
            }

            if (!targetUrl) {
                return true;
            }

            const normalizedTarget = targetUrl.trim();
            if (!normalizedTarget || normalizedTarget.startsWith('#') || normalizedTarget.startsWith('javascript:')) {
                return false;
            }

            return true;
        }

        function setupUnsavedChangesProtection() {
            const form = document.getElementById('{{ $formId }}');
            const floatingSubmitButton = document.getElementById('floating_save_submit');

            if (floatingSubmitButton && form) {
                floatingSubmitButton.addEventListener('click', function() {
                    form.requestSubmit();
                });
            }

            document.addEventListener('input', function(e) {
                if (e.target.closest('#{{ $formId }}')) {
                    markDirty();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.closest('#{{ $formId }}')) {
                    markDirty();
                }
            });

            @if (!empty($trackSummernote))
            $(document).on('summernote.change', '.richtext-editor', function() {
                markDirty();
            });
            @endif

            window.addEventListener('beforeunload', function(e) {
                if (!shouldBlockNavigation(window.location.href)) {
                    return;
                }

                e.preventDefault();
                e.returnValue = unsavedChangesMessage;
            });

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link) {
                    return;
                }

                const href = link.getAttribute('href') || '';
                if (!shouldBlockNavigation(href)) {
                    return;
                }

                e.preventDefault();
                alert(unsavedChangesMessage);
            }, true);
        }
