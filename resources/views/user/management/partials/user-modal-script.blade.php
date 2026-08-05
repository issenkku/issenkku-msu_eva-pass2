<script>
    (function () {
        const modal = document.getElementById('userModal');
        const form = document.getElementById('userForm');
        const methodInput = document.getElementById('formMethod');
        const passwordPanel = document.getElementById('passwordPanel');
        const passwordInput = document.getElementById('password');
        const educationRows = document.getElementById('educationHistoryRows');
        const currentRoleDisplay = document.getElementById('currentRoleDisplay');
        const modalTitle = modal?.querySelector('h2');

        const storeAction = @json(route('users.store'));
        const updateActionTemplate = @json(route('users.update', ':id'));
        const checkUniqueUrl = @json(route('users.users.check-unique'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let currentUserId = null;

        const fieldNames = [
            'prefix',
            'name',
            'employee_id',
            'email',
            'phone',
            'department_id',
            'position_id',
            'job_level_id',
            'personnel_type',
            'status',
        ];

        function decodeUserPayload(encoded) {
            const binary = atob(encoded);
            const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
            return JSON.parse(new TextDecoder().decode(bytes));
        }

        function setValue(name, value) {
            const field = document.getElementById(name);
            if (field) {
                field.value = value ?? '';
            }
        }

        function errorElement(field) {
            return document.getElementById(`${field}Error`);
        }

        function setFieldError(field, message = '') {
            const element = errorElement(field);
            const input = document.getElementById(field);

            if (!element) {
                return;
            }

            element.textContent = message;
            element.classList.toggle('hidden', !message);
            input?.classList.toggle('border-red-500', Boolean(message));
        }

        function clearFieldErrors() {
            ['prefix', 'name', 'employee_id', 'email', 'phone', 'department_id', 'position_id', 'personnel_type', 'password'].forEach((field) => {
                setFieldError(field);
            });
        }

        async function checkUnique(field) {
            const input = document.getElementById(field);
            const value = input?.value?.trim();

            if (!value) {
                return true;
            }

            const response = await fetch(checkUniqueUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    field,
                    value,
                    user_id: currentUserId,
                }),
            });

            if (!response.ok) {
                return true;
            }

            const result = await response.json();

            if (!result.unique) {
                const labels = {
                    name: 'ชื่อ-นามสกุลนี้ถูกใช้ไปแล้ว',
                    employee_id: 'รหัสพนักงานนี้ถูกใช้ไปแล้ว',
                    email: 'อีเมลนี้ถูกใช้ไปแล้ว',
                    phone: 'เบอร์โทรนี้ถูกใช้ไปแล้ว',
                };

                setFieldError(field, labels[field] || result.message);
                return false;
            }

            setFieldError(field);
            return true;
        }

        async function validateBeforeSubmit() {
            clearFieldErrors();

            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            if (passwordInput?.required && passwordInput.value.length < 6) {
                setFieldError('password', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                passwordInput.focus();
                return false;
            }

            const checks = await Promise.all([
                checkUnique('name'),
                checkUnique('employee_id'),
                checkUnique('email'),
                checkUnique('phone'),
            ]);

            const firstInvalidField = ['name', 'employee_id', 'email', 'phone']
                .find((field, index) => !checks[index]);

            if (firstInvalidField) {
                document.getElementById(firstInvalidField)?.focus();
                return false;
            }

            return true;
        }

        function selectedRoles() {
            return Array.from(document.querySelectorAll('#roles input[type="checkbox"]:checked'))
                .map((input) => input.value);
        }

        function updateRoleDisplay() {
            if (!currentRoleDisplay) {
                return;
            }

            const roles = selectedRoles();
            currentRoleDisplay.textContent = roles.length ? roles.join(', ') : 'ยังไม่ได้เลือกบทบาท';
        }

        function resetRoleCheckboxes(roles = []) {
            const roleNames = roles.map((role) => typeof role === 'string' ? role : role.name);

            document.querySelectorAll('#roles input[type="checkbox"]').forEach((input) => {
                input.checked = roleNames.includes(input.value);
            });

            updateRoleDisplay();
        }

        function educationRowTemplate(index, entry = {}) {
            return `
                <div class="grid grid-cols-1 gap-3 rounded border border-gray-200 p-3 md:grid-cols-4" data-education-row>
                    <input
                        type="text"
                        name="education_history[${index}][graduation_year]"
                        value="${entry.graduation_year ?? ''}"
                        class="rounded border px-3 py-2"
                        maxlength="4"
                        inputmode="numeric"
                        placeholder="ปีที่จบ">
                    <input
                        type="text"
                        name="education_history[${index}][degree]"
                        value="${entry.degree ?? ''}"
                        class="rounded border px-3 py-2 md:col-span-1"
                        placeholder="วุฒิการศึกษา">
                    <input
                        type="text"
                        name="education_history[${index}][university]"
                        value="${entry.university ?? ''}"
                        class="rounded border px-3 py-2 md:col-span-1"
                        placeholder="มหาวิทยาลัย">
                    <button
                        type="button"
                        class="rounded border border-red-300 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                        data-user-education-remove>
                        ลบ
                    </button>
                </div>
            `;
        }

        function setEducationRows(entries = []) {
            if (!educationRows) {
                return;
            }

            educationRows.innerHTML = '';
            const rows = Array.isArray(entries) && entries.length ? entries : [{}];
            rows.forEach((entry, index) => {
                educationRows.insertAdjacentHTML('beforeend', educationRowTemplate(index, entry));
            });
        }

        window.reindexEducationHistoryRows = function () {
            if (!educationRows) {
                return;
            }

            educationRows.querySelectorAll('[data-education-row]').forEach((row, rowIndex) => {
                row.querySelectorAll('input').forEach((input) => {
                    input.name = input.name.replace(/education_history\[\d+]/, `education_history[${rowIndex}]`);
                });
            });
        };

        window.addEducationHistoryRow = function (entry = {}) {
            if (!educationRows) {
                return;
            }

            const index = educationRows.querySelectorAll('[data-education-row]').length;
            educationRows.insertAdjacentHTML('beforeend', educationRowTemplate(index, entry));
        };

        window.closeModal = function () {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        function removeEducationRow(button) {
            const row = button.closest('[data-education-row]');
            if (row) {
                row.remove();
                window.reindexEducationHistoryRows();
            }
        }

        window.openCreateModal = function (button = null) {
            if (!modal || !form || !methodInput) {
                return;
            }

            form.reset();
            form.action = button?.dataset?.action || storeAction;
            methodInput.value = 'POST';
            currentUserId = null;

            if (modalTitle) {
                modalTitle.textContent = 'เพิ่มผู้ใช้งานใหม่';
            }

            if (passwordPanel) {
                passwordPanel.classList.remove('hidden');
            }

            if (passwordInput) {
                passwordInput.required = true;
            }

            fieldNames.forEach((name) => setValue(name, ''));
            setValue('status', 'active');
            resetRoleCheckboxes([]);
            setEducationRows([]);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.openEditModalFromButton = function (button) {
            if (!modal || !form || !methodInput || !button?.dataset?.user) {
                return;
            }

            const user = decodeUserPayload(button.dataset.user);

            form.reset();
            form.action = updateActionTemplate.replace(':id', user.id);
            methodInput.value = 'PUT';
            currentUserId = user.id;

            if (modalTitle) {
                modalTitle.textContent = 'แก้ไขข้อมูลผู้ใช้งาน';
            }

            if (passwordPanel) {
                passwordPanel.classList.add('hidden');
            }

            if (passwordInput) {
                passwordInput.required = false;
                passwordInput.value = '';
            }

            fieldNames.forEach((name) => setValue(name, user[name]));
            resetRoleCheckboxes(user.roles || []);
            setEducationRows(user.education_history || []);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        document.querySelectorAll('#roles input[type="checkbox"]').forEach((input) => {
            input.addEventListener('change', updateRoleDisplay);
        });

        ['name', 'employee_id', 'email', 'phone'].forEach((field) => {
            document.getElementById(field)?.addEventListener('input', () => setFieldError(field));
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!await validateBeforeSubmit()) {
                return;
            }

            const coordinator = window.MasterDataPage?.createMasterDataSubmitCoordinator({
                applyMutation: async () => {
                    await window.AsyncResourceTable.refreshTableRegion(
                        window.location.href,
                        '[data-async-table-region]',
                    );
                    window.MasterDataPage.initializeAsyncDeleteForms(document);
                },
                applyValidationErrors: (errors) => {
                    Object.entries(errors || {}).forEach(([name, messages]) => {
                        const field = name.split('.')[0];
                        setFieldError(field, Array.isArray(messages) ? messages[0] : String(messages));
                    });
                },
                button: form.querySelector('button[type="submit"]'),
                form,
                hideModal: window.closeModal,
            });

            if (coordinator) {
                await coordinator({ preventDefault() {} });
            } else {
                form.submit();
            }
        });

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeModal();
            }
        });

        document.addEventListener('click', (event) => {
            const closeButton = event.target.closest('[data-user-modal-close]');
            if (closeButton) {
                event.preventDefault();
                window.closeModal();
                return;
            }

            const createButton = event.target.closest('[data-create-modal-open]');
            if (createButton) {
                event.preventDefault();
                window.openCreateModal(createButton);
                return;
            }

            const editButton = event.target.closest('[data-user-edit-trigger]');
            if (editButton) {
                event.preventDefault();
                window.openEditModalFromButton(editButton);
                return;
            }

            const deleteButton = event.target.closest('[data-user-delete-trigger]');
            if (deleteButton) {
                event.preventDefault();
                const userId = deleteButton.dataset.userId;
                if (userId && typeof window.confirmDelete === 'function') {
                    window.confirmDelete(userId);
                }
                return;
            }

            const addEducationButton = event.target.closest('[data-user-education-add]');
            if (addEducationButton) {
                event.preventDefault();
                window.addEducationHistoryRow();
                return;
            }

            const removeEducationButton = event.target.closest('[data-user-education-remove]');
            if (removeEducationButton) {
                event.preventDefault();
                removeEducationRow(removeEducationButton);
            }
        });

        setEducationRows([]);
        updateRoleDisplay();
    })();
</script>
