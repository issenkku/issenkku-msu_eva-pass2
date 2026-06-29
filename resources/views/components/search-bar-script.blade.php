<script>
    (function () {
        if (window.__searchBarClearBound) {
            return;
        }

        window.__searchBarClearBound = true;

        document.addEventListener('click', function (event) {
            const clearButton = event.target.closest('[data-auto-search-clear]');
            if (!clearButton) {
                return;
            }

            const form = clearButton.closest('[data-auto-search-form]');
            if (!form) {
                return;
            }

            const input = form.querySelector('[data-auto-search-input]');
            if (!input) {
                return;
            }

            input.value = '';
            form.requestSubmit();
        });
    })();
</script>
