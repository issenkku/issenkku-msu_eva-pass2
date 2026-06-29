<script>
    (function () {
        if (window.__autoSubmitSelectBound) {
            return;
        }

        window.__autoSubmitSelectBound = true;

        document.addEventListener('change', function (event) {
            const control = event.target.closest('[data-auto-submit-select]');
            if (!control || !control.form) {
                return;
            }

            control.form.requestSubmit();
        });
    })();
</script>
