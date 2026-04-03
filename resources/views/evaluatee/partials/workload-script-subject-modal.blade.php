<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectModalEl = document.getElementById('subjectModal');
        const openLinks = document.querySelectorAll('.workload-open-subject-modal');

        if (!subjectModalEl || openLinks.length === 0 || typeof bootstrap === 'undefined') {
            return;
        }

        const subjectModal = bootstrap.Modal.getOrCreateInstance(subjectModalEl, {
            backdrop: false,
            focus: false,
        });

        function setBackdropInert(isInert) {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function (backdrop) {
                if (isInert) {
                    backdrop.classList.add('workload-backdrop-inert');
                } else {
                    backdrop.classList.remove('workload-backdrop-inert');
                }
            });
        }

        subjectModalEl.addEventListener('show.bs.modal', function () {
            setBackdropInert(true);
        });

        subjectModalEl.addEventListener('hidden.bs.modal', function () {
            setBackdropInert(false);
        });

        openLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const form = document.getElementById('subjectForm');
                if (form) {
                    form.action = "{{ route('subjects.store.evaluatee') }}?redirect_to=" + encodeURIComponent(window.location.href);
                    const methodInput = document.getElementById('form_method');
                    if (methodInput) {
                        methodInput.value = 'POST';
                    }
                    const redirectInput = document.getElementById('subjectRedirectTo');
                    if (redirectInput) {
                        redirectInput.value = window.location.href;
                    }
                }
                subjectModal.show();
            });
        });
    });
</script>
