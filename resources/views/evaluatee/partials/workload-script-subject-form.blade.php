<script>
    document.addEventListener('DOMContentLoaded', function () {
        const creditsLabel = document.querySelector('#subjectModal label[for="credits"]');
        if (creditsLabel) {
            creditsLabel.innerHTML = 'หน่วยกิต <span class="text-danger">*</span>';
        }

        const creditsError = document.getElementById('creditsError');
        if (creditsError) {
            creditsError.textContent = 'กรุณากรอกหน่วยกิต';
        }

        if (typeof window.submitForm === 'function') {
            return;
        }

        function toggleError(input, errorId, isValid) {
            const errorEl = document.getElementById(errorId);
            input.classList.toggle('is-invalid', !isValid);

            if (errorEl) {
                errorEl.style.display = isValid ? 'none' : 'block';
            }
        }

        function validateSubjectForm() {
            const codeInput = document.getElementById('code');
            const nameThInput = document.getElementById('name_th');
            const lectureCreditsInput = document.getElementById('lecture_credits');
            const labCreditsInput = document.getElementById('lab_credits');
            const selfStudyCreditsInput = document.getElementById('self_study_credits');
            const creditsInput = document.getElementById('credits');

            if (!codeInput || !nameThInput || !lectureCreditsInput || !labCreditsInput || !selfStudyCreditsInput || !creditsInput) {
                return true;
            }

            const codeValid = codeInput.value.trim() !== '';
            const nameValid = nameThInput.value.trim() !== '';
            const lectureValid = lectureCreditsInput.value.trim() !== '' && !Number.isNaN(Number(lectureCreditsInput.value));
            const labValid = labCreditsInput.value.trim() !== '' && !Number.isNaN(Number(labCreditsInput.value));
            const selfStudyValid = selfStudyCreditsInput.value.trim() !== '' && !Number.isNaN(Number(selfStudyCreditsInput.value));
            const creditsValid = creditsInput.value.trim() !== '' && !Number.isNaN(Number(creditsInput.value));

            toggleError(codeInput, 'codeError', codeValid);
            toggleError(nameThInput, 'nameThError', nameValid);
            toggleError(lectureCreditsInput, 'lectureCreditsError', lectureValid);
            toggleError(labCreditsInput, 'labCreditsError', labValid);
            toggleError(selfStudyCreditsInput, 'selfStudyCreditsError', selfStudyValid);
            toggleError(creditsInput, 'creditsError', creditsValid);

            return codeValid && nameValid && lectureValid && labValid && selfStudyValid && creditsValid;
        }

        window.submitForm = function () {
            const form = document.getElementById('subjectForm');
            if (!form) {
                return;
            }

            const redirectInput = document.getElementById('subjectRedirectTo');
            if (redirectInput) {
                redirectInput.value = window.location.href;
            }

            form.action = "{{ route('subjects.store.evaluatee') }}?redirect_to=" + encodeURIComponent(window.location.href);

            if (!validateSubjectForm()) {
                return;
            }

            form.submit();
        };

    });
</script>
