<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-auto-search-form]').forEach(function (form) {
            const input = form.querySelector('[data-auto-search-input]');
            const clearButton = form.querySelector('[data-auto-search-clear]');

            if (!input) {
                return;
            }

            if (clearButton) {
                clearButton.addEventListener('click', function () {
                    input.value = '';
                    form.requestSubmit();
                });
            }

            let debounceTimer;
            let isComposing = false;
            const initialValue = input.value;

            function submitIfChanged() {
                const currentValue = input.value.trim();
                const previousValue = initialValue.trim();

                if (currentValue === previousValue) {
                    return;
                }

                form.requestSubmit();
            }

            input.addEventListener('compositionstart', function () {
                isComposing = true;
            });

            input.addEventListener('compositionend', function () {
                isComposing = false;
            });

            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);

                if (isComposing) {
                    return;
                }

                debounceTimer = setTimeout(function () {
                    submitIfChanged();
                }, 1000);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                clearTimeout(debounceTimer);
                submitIfChanged();
            });
        });
    });
</script>

@include('components.auto-submit-script')
