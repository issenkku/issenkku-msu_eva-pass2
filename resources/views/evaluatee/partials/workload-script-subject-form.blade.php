<script>
    document.addEventListener('DOMContentLoaded', function () {
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
            const lectureHoursInput = document.getElementById('lecture_hours');
            const labHoursInput = document.getElementById('lab_hours');
            const selfStudyHoursInput = document.getElementById('self_study_hours');
            const creditsInput = document.getElementById('credits');

            if (!codeInput || !nameThInput || !lectureCreditsInput || !labCreditsInput || !selfStudyCreditsInput ||
                !lectureHoursInput || !labHoursInput || !selfStudyHoursInput || !creditsInput) {
                return true;
            }

            [
                lectureCreditsInput, labCreditsInput, selfStudyCreditsInput, creditsInput,
                lectureHoursInput, labHoursInput, selfStudyHoursInput,
            ].forEach(function (input) {
                if (input.value.trim() === '') input.value = '0';
            });

            const codeValid = codeInput.value.trim() !== '';
            const nameValid = nameThInput.value.trim() !== '';
            const lectureValid = Number.isInteger(Number(lectureCreditsInput.value)) && Number(lectureCreditsInput.value) >= 0;
            const labValid = Number.isInteger(Number(labCreditsInput.value)) && Number(labCreditsInput.value) >= 0;
            const selfStudyValid = Number.isInteger(Number(selfStudyCreditsInput.value)) && Number(selfStudyCreditsInput.value) >= 0;
            const creditsValid = Number.isInteger(Number(creditsInput.value)) && Number(creditsInput.value) >= 0;
            const lectureHoursValid = Number.isInteger(Number(lectureHoursInput.value)) && Number(lectureHoursInput.value) >= 0;
            const labHoursValid = Number.isInteger(Number(labHoursInput.value)) && Number(labHoursInput.value) >= 0;
            const selfStudyHoursValid = Number.isInteger(Number(selfStudyHoursInput.value)) && Number(selfStudyHoursInput.value) >= 0;

            toggleError(codeInput, 'codeError', codeValid);
            toggleError(nameThInput, 'nameThError', nameValid);
            toggleError(lectureCreditsInput, 'lectureCreditsError', lectureValid);
            toggleError(labCreditsInput, 'labCreditsError', labValid);
            toggleError(selfStudyCreditsInput, 'selfStudyCreditsError', selfStudyValid);
            toggleError(creditsInput, 'creditsError', creditsValid);
            toggleError(lectureHoursInput, 'lectureHoursError', lectureHoursValid);
            toggleError(labHoursInput, 'labHoursError', labHoursValid);
            toggleError(selfStudyHoursInput, 'selfStudyHoursError', selfStudyHoursValid);

            return codeValid && nameValid && lectureValid && labValid && selfStudyValid && creditsValid &&
                lectureHoursValid && labHoursValid && selfStudyHoursValid;
        }

        window.submitForm = async function () {
            const form = document.getElementById('subjectForm');
            if (!form) {
                return;
            }

            const redirectInput = document.getElementById('subjectRedirectTo');
            if (redirectInput) {
                redirectInput.value = window.location.href;
            }

            form.action = "{{ route('subjects.store.evaluatee') }}";

            if (!validateSubjectForm()) {
                return;
            }

            const submitButton = document.getElementById('subjectSubmitBtn');
            const coordinator = window.AsyncForm?.createAsyncFormCoordinator({
                applySuccess: async function (payload) {
                    document.dispatchEvent(new CustomEvent('workload:subject-created', {
                        detail: { subject: payload.data.subject },
                    }));
                    const modalElement = document.getElementById('subjectModal');
                    if (modalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                    }
                    form.reset();
                },
                applyValidationErrors: function (errors) {
                    const errorIds = {
                        code: 'codeError',
                        name_th: 'subjectNameError',
                        name_en: 'subjectNameError',
                        credits: 'creditsError',
                        lecture_credits: 'lectureCreditsError',
                        lab_credits: 'labCreditsError',
                        self_study_credits: 'selfStudyCreditsError',
                        lecture_hours: 'lectureHoursError',
                        lab_hours: 'labHoursError',
                        self_study_hours: 'selfStudyHoursError',
                    };
                    Object.entries(errors || {}).forEach(function ([name, messages]) {
                        const input = form.querySelector('[name="' + name + '"]');
                        const errorElement = document.getElementById(errorIds[name]);
                        if (input) input.classList.add('is-invalid');
                        if (errorElement) {
                            errorElement.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                            errorElement.style.display = 'block';
                        }
                    });
                },
                form,
                getSubmitButton: function () { return submitButton; },
                request: window.AsyncForm.requestFormMutation,
                showMessage: window.MasterDataPage.showMasterDataMessage,
            });

            if (coordinator) {
                await coordinator({ preventDefault: function () {} });
            } else {
                form.submit();
            }
        };

        document.getElementById('subjectSubmitBtn')?.addEventListener('click', window.submitForm);
    });
</script>
