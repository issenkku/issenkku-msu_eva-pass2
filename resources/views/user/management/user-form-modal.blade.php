<div id="userModal" class="fixed inset-0 z-[9999] hidden items-baseline justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative top-10 w-full max-w-3xl rounded-xl bg-white p-6 max-h-[90vh] overflow-y-auto">
        @include('user.management.partials.user-modal-header')

        <form id="userForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            @include('user.management.partials.user-modal-error-list')

            <div class="mt-4 grid grid-cols-1 gap-6">
                @include('user.management.partials.user-modal-personal-section')
                @include('user.management.partials.user-modal-work-section')
                @include('user.management.partials.user-modal-contact-section')
                @include('user.management.partials.user-modal-education-section')
                @include('user.management.partials.user-modal-password-section')
                @include('user.management.partials.user-modal-settings-section')
            </div>

            @include('user.management.partials.user-modal-actions')
        </form>
    </div>
</div>

<script>
function setSelectedRoles(roleSelect, roles) {
    const selectedRoles = Array.isArray(roles) ? roles : [];
    const checkboxes = roleSelect.querySelectorAll('input[type="checkbox"][name="roles[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectedRoles.includes(checkbox.value);
    });
    roleSelect.dispatchEvent(new Event('change'));
}

function setModalTitle(title) {
    const modal = document.getElementById('userModal');
    const modalTitle = modal.querySelector('h2');
    if (modalTitle) {
        modalTitle.textContent = title;
    }
}

function educationHistoryRowTemplate(index, entry = {}) {
    return `
        <div class="grid grid-cols-1 gap-3 rounded border border-gray-200 p-3 md:grid-cols-[140px_1fr_1fr_auto]">
            <input type="text" name="education_history[${index}][graduation_year]" value="${entry.graduation_year ?? ''}" placeholder="ปีที่จบ" maxlength="4" class="w-full rounded border px-3 py-2">
            <input type="text" name="education_history[${index}][degree]" value="${entry.degree ?? ''}" placeholder="วุฒิการศึกษา" class="w-full rounded border px-3 py-2">
            <input type="text" name="education_history[${index}][university]" value="${entry.university ?? ''}" placeholder="มหาวิทยาลัยที่จบ" class="w-full rounded border px-3 py-2">
            <button type="button" onclick="removeEducationHistoryRow(this)" class="rounded border border-red-300 px-3 py-2 text-sm text-red-600 hover:bg-red-50">ลบ</button>
        </div>
    `;
}

function renderEducationHistoryRows(entries = []) {
    const container = document.getElementById('educationHistoryRows');
    const normalizedEntries = Array.isArray(entries) && entries.length > 0 ? entries : [{}];
    container.innerHTML = normalizedEntries.map((entry, index) => educationHistoryRowTemplate(index, entry)).join('');
}

function addEducationHistoryRow(entry = {}) {
    const container = document.getElementById('educationHistoryRows');
    const index = container.children.length;
    container.insertAdjacentHTML('beforeend', educationHistoryRowTemplate(index, entry));
}

function removeEducationHistoryRow(button) {
    const container = document.getElementById('educationHistoryRows');
    button.closest('div.grid').remove();

    const rows = Array.from(container.children).map(row => ({
        graduation_year: row.querySelector('[name$="[graduation_year]"]')?.value ?? '',
        degree: row.querySelector('[name$="[degree]"]')?.value ?? '',
        university: row.querySelector('[name$="[university]"]')?.value ?? '',
    }));

    renderEducationHistoryRows(rows);
}

function openCreateModal(button) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const roleSelect = document.getElementById('roles');

    form.reset();
    form.action = button.getAttribute('data-action');
    form.removeAttribute('data-user-id');
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('passwordPanel').style.display = 'block';
    document.getElementById('password').required = true;
    renderEducationHistoryRows([]);

    setSelectedRoles(roleSelect, []);
    setModalTitle('เพิ่มผู้ใช้งานใหม่');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openEditModal(user) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const roleSelect = document.getElementById('roles');

    form.reset();
    form.action = `/users/${user.id}`;
    form.setAttribute('data-user-id', user.id);
    document.getElementById('formMethod').value = 'PUT';

    ['prefix', 'name', 'employee_id', 'department_id', 'position_id', 'job_level_id', 'personnel_type', 'email', 'phone', 'status']
        .forEach(field => {
            const input = document.getElementById(field);
            if (input && user[field] !== undefined) {
                input.value = user[field] ?? '';
            }
        });

    renderEducationHistoryRows(user.education_history && user.education_history.length > 0
        ? user.education_history
        : (user.bio ? [{ degree: user.bio }] : []));

    document.getElementById('passwordPanel').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('password').value = '';

    setSelectedRoles(roleSelect, (user.roles || []).map(role => role.name));
    setModalTitle('แก้ไขข้อมูลผู้ใช้งาน');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openEditModalFromButton(button) {
    const payload = button.getAttribute('data-user');
    if (!payload) {
        return;
    }

    const binary = atob(payload);
    const bytes = Uint8Array.from(binary, char => char.charCodeAt(0));
    const json = new TextDecoder('utf-8').decode(bytes);
    openEditModal(JSON.parse(json));
}

function closeModal() {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    form.reset();
    form.removeAttribute('data-user-id');
    document.getElementById('passwordPanel').style.display = 'block';
    document.getElementById('password').required = true;
    document.querySelectorAll('[id$="Error"]').forEach(err => err.classList.add('hidden'));
    renderEducationHistoryRows([]);

    setSelectedRoles(document.getElementById('roles'), []);
    setModalTitle('เพิ่มผู้ใช้งานใหม่');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showError(id, message) {
    const errorDiv = document.getElementById(`${id}Error`);
    const input = document.getElementById(id);

    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    if (input) {
        input.classList.add('border-red-500');
    }
}

function hideError(id) {
    const errorDiv = document.getElementById(`${id}Error`);
    const input = document.getElementById(id);

    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }

    if (input) {
        input.classList.remove('border-red-500');
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

async function checkUnique(field, value) {
    const form = document.getElementById('userForm');
    const userId = form.getAttribute('data-user-id');

    if (!value || value.trim() === '') {
        return { unique: true };
    }

    const response = await fetch('/users/check-unique', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            field,
            value,
            user_id: userId
        })
    });

    return response.json();
}

const validateUniqueField = debounce(async (id, fieldName, label) => {
    const input = document.getElementById(id);
    const value = input.value.trim();

    if (!value) {
        hideError(id);
        return;
    }

    const result = await checkUnique(fieldName, value);
    if (!result.unique) {
        showError(id, `${label}นี้ถูกใช้งานแล้ว`);
        return;
    }

    hideError(id);
}, 400);

document.addEventListener('DOMContentLoaded', () => {
    const oldEducationHistory = @json(old('education_history', []));
    const roleSelect = document.getElementById('roles');
    const currentRoleDisplay = document.getElementById('currentRoleDisplay');

    renderEducationHistoryRows(oldEducationHistory);

    roleSelect.addEventListener('change', function() {
        const selectedRoles = Array.from(
            this.querySelectorAll('input[type="checkbox"][name="roles[]"]:checked')
        ).map(option => option.value);
        currentRoleDisplay.textContent = selectedRoles.length > 0
            ? `บทบาทที่เลือก: ${selectedRoles.join(', ')}`
            : 'ยังไม่ได้เลือกบทบาท';
    });
    roleSelect.dispatchEvent(new Event('change'));

    roleSelect.querySelectorAll('input[type="checkbox"][name="roles[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            roleSelect.dispatchEvent(new Event('change'));
        });
    });

    document.getElementById('phone').addEventListener('input', function() {
        let digits = this.value.replace(/\D/g, '').slice(0, 10);
        if (digits.length > 6) {
            this.value = `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
        } else if (digits.length > 3) {
            this.value = `${digits.slice(0, 3)}-${digits.slice(3)}`;
        } else {
            this.value = digits;
        }
    });

    document.getElementById('employee_id').addEventListener('input', () => validateUniqueField('employee_id', 'employee_id', 'รหัสพนักงาน'));
    document.getElementById('email').addEventListener('input', () => validateUniqueField('email', 'email', 'อีเมล'));
    document.getElementById('phone').addEventListener('input', () => validateUniqueField('phone', 'phone', 'เบอร์โทร'));

    document.getElementById('userForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        let hasError = false;
        const requiredFields = ['prefix', 'name', 'employee_id', 'department_id', 'position_id', 'personnel_type', 'email', 'phone', 'status'];
        requiredFields.forEach(id => {
            const input = document.getElementById(id);
            if (!input.value.trim()) {
                showError(id, 'จำเป็นต้องกรอกข้อมูล');
                hasError = true;
            } else {
                hideError(id);
            }
        });

        const passwordInput = document.getElementById('password');
        if (passwordInput.required && passwordInput.value.trim().length < 6) {
            showError('password', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
            hasError = true;
        } else {
            hideError('password');
        }

        if (hasError) {
            return;
        }

        const employeeIdCheck = await checkUnique('employee_id', document.getElementById('employee_id').value);
        const emailCheck = await checkUnique('email', document.getElementById('email').value);
        const phoneCheck = await checkUnique('phone', document.getElementById('phone').value.replace(/\D/g, ''));

        if (!employeeIdCheck.unique) {
            showError('employee_id', 'รหัสพนักงานนี้ถูกใช้งานแล้ว');
            hasError = true;
        }
        if (!emailCheck.unique) {
            showError('email', 'อีเมลนี้ถูกใช้งานแล้ว');
            hasError = true;
        }
        if (!phoneCheck.unique) {
            showError('phone', 'เบอร์โทรนี้ถูกใช้งานแล้ว');
            hasError = true;
        }

        if (!hasError) {
            this.submit();
        }
    });

    if ({{ $errors->any() ? 'true' : 'false' }}) {
        const oldRoles = @json(old('roles', isset($user) && $user ? $user->roles->pluck('name')->toArray() : []));
        setSelectedRoles(roleSelect, oldRoles);
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('userModal').classList.add('flex');
    }
});
</script>
