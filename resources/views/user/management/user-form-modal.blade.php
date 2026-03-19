<div id="userModal" class="fixed inset-0 z-[9999] hidden items-baseline justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative top-10 w-full max-w-3xl rounded-xl bg-white p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3">
            <h2 class="text-lg font-semibold text-purple-700">เพิ่มผู้ใช้งานใหม่</h2>
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-red-500">&times;</button>
        </div>

        <form id="userForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            @if ($errors->any())
                <div class="mb-4 mt-4 rounded border border-red-400 bg-red-100 p-3 text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-4 grid grid-cols-1 gap-6">
                <div>
                    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลส่วนบุคคล</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block">คำนำหน้า <span class="text-red-600">*</span></label>
                            <input type="text" name="prefix" id="prefix" class="w-full rounded border px-3 py-2" required list="user-prefix-options" placeholder="เช่น นาย, อ.ดร., ว่าที่ ร.ต.">
                            <datalist id="user-prefix-options">
                                <option value="นาย">
                                <option value="นาง">
                                <option value="นางสาว">
                                <option value="อ.ดร.">
                                <option value="ผศ.ดร.">
                                <option value="รศ.ดร.">
                                <option value="ศ.ดร.">
                                <option value="ว่าที่ ร.ต.">
                                <option value="ว่าที่พันตรี">
                            </datalist>
                            <div class="mt-1 hidden text-sm text-red-500" id="prefixError">จำเป็นต้องกรอกข้อมูล</div>
                            <div class="mt-1 text-xs text-gray-500">กรอกคำนำหน้าแบบกำหนดเองได้</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block">ชื่อ-นามสกุล <span class="text-red-600">*</span></label>
                            <input type="text" name="name" id="name" class="w-full rounded border px-3 py-2" required>
                            <div class="mt-1 hidden text-sm text-red-500" id="nameError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block">รหัสพนักงาน <span class="text-red-600">*</span></label>
                            <input type="text" name="employee_id" id="employee_id" class="w-full rounded border px-3 py-2" required pattern="[0-9]+" inputmode="numeric">
                            <div class="mt-1 hidden text-sm text-red-500" id="employee_idError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลงาน</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block">หน่วยงาน <span class="text-red-600">*</span></label>
                            <select name="department_id" id="department_id" class="w-full rounded border px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกหน่วยงาน--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 hidden text-sm text-red-500" id="department_idError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                        <div>
                            <label class="block">ตำแหน่ง <span class="text-red-600">*</span></label>
                            <select name="position_id" id="position_id" class="w-full rounded border px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกตำแหน่ง--</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 hidden text-sm text-red-500" id="position_idError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                        <div>
                            <label class="block">ประเภทบุคลากร <span class="text-red-600">*</span></label>
                            <select name="personnel_type" id="personnel_type" class="w-full rounded border px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกประเภทบุคลากร--</option>
                                <option value="สนับสนุน">สนับสนุน</option>
                                <option value="วิชาการ">วิชาการ</option>
                                <option value="บริหาร">บริหาร</option>
                            </select>
                            <div class="mt-1 hidden text-sm text-red-500" id="personnel_typeError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลติดต่อ</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block">อีเมล <span class="text-red-600">*</span></label>
                            <input type="email" name="email" id="email" class="w-full rounded border px-3 py-2" required>
                            <div class="mt-1 hidden text-sm text-red-500" id="emailError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                        <div>
                            <label class="block">เบอร์โทร <span class="text-red-600">*</span></label>
                            <input type="text" name="phone" id="phone" class="w-full rounded border px-3 py-2" required>
                            <div class="mt-1 hidden text-sm text-red-500" id="phoneError">จำเป็นต้องกรอกข้อมูล</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold text-purple-600">ประวัติการศึกษา</h3>
                    <div id="educationHistoryRows" class="space-y-3"></div>
                    <button type="button" onclick="addEducationHistoryRow()" class="mt-3 rounded border border-purple-300 px-3 py-2 text-sm text-purple-700 hover:bg-purple-50">
                        เพิ่มวุฒิการศึกษา
                    </button>
                </div>

                <div id="passwordPanel">
                    <h3 class="mb-2 font-semibold text-purple-600">รหัสผ่าน <span class="text-red-600">*</span></h3>
                    <input type="password" name="password" id="password" class="w-full rounded border px-3 py-2">
                    <div class="mt-1 hidden text-sm text-red-500" id="passwordError">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</div>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold text-purple-600">ตั้งค่าผู้ใช้งาน</h3>
                    <div class="mb-3">
                        <label class="block">สถานะ <span class="text-red-600">*</span></label>
                        <select name="status" id="status" class="w-full rounded border px-3 py-2" required>
                            <option value="active">active</option>
                            <option value="inactive">inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block">บทบาท</label>
                        <div id="roles" class="grid grid-cols-1 gap-2 rounded border px-3 py-3 md:grid-cols-2">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->name }}"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span>{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-2 text-xs text-gray-500">เลือกได้มากกว่าหนึ่งบทบาท</div>
                        <div id="currentRoleDisplay" class="mt-2 text-sm text-green-700">ยังไม่ได้เลือกบทบาท</div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-center gap-4">
                <x-button type= defualt text="ย้อนกลับ" onclick="closeModal()" icon="fas fa-arrow-left" />
                <x-button type="primary" text="บันทึก" icon="fas fa-save" buttonType="submit" />
            </div>
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

    ['prefix', 'name', 'employee_id', 'department_id', 'position_id', 'personnel_type', 'email', 'phone', 'status']
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
