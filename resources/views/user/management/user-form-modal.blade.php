<!-- Modal Trigger Button (optional) -->
<!-- <x-button text="เพิ่มเจ้าหน้าที่ใหม่" onclick="openModal()" /> -->

<!-- Modal Background -->
<div id="userModal" class="fixed z-[9999] inset-0 bg-black bg-opacity-50 hidden items-baseline justify-center z-50 overflow-y-auto">
    <!-- Modal Box -->
    <div class="top-10 bg-white rounded-xl w-full max-w-3xl p-6 relative max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-3">
            <h2 class="text-lg font-semibold text-purple-700">เพิ่มเจ้าหน้าที่ใหม่</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-red-500">&times;</button>
        </div>

        <!-- Form -->
        <form id="userForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 mt-4">
                <!-- ข้อมูลส่วนบุคคล -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลส่วนบุคคล</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block">คำนำหน้า <span style="color: #dc3545;">*</span></label>
                            <select name="prefix" id="prefix" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกคำนำหน้า--</option>
                                <option value="นาย" {{ old('prefix', $user->prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix', $user->prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix', $user->prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="prefixError">กรุณาเลือกคำนำหน้า</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block">ชื่อ-นามสกุล <span style="color: #dc3545;">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border rounded px-3 py-2" 
                                placeholder="กรุณากรอกชื่อ-นามสกุล"
                                required />
                            <div class="text-red-500 text-sm mt-1 hidden" id="nameError">กรุณากรอกชื่อ</div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block">รหัสพนักงาน <span style="color: #dc3545;">*</span></label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id ?? '') }}" class="w-full border rounded px-3 py-2" 
                                placeholder="กรุณากรอกรหัสพนักงาน"
                                required pattern="[0-9]+" inputmode="numeric" />
                            <div class="text-red-500 text-sm mt-1 hidden" id="employee_idError">กรุณากรอกรหัสพนักงานให้ถูกต้อง</div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลงาน -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลงาน</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label>สาขาวิชา <span style="color: #dc3545;">*</span></label>
                            <select name="department_id" id="department_id" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกสาขาวิชา--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="department_idError">กรุณาเลือกสาขาวิชา</div>
                        </div>
                        <div>
                            <label>ตำแหน่ง <span style="color: #dc3545;">*</span></label>
                            <select name="position_id" id="position_id" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกตำแหน่ง--</option>
                                @foreach ($positions as $position) 
                                    <option value="{{ $position->id }}"
                                        {{ old('position_id', $user->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="position_idError">กรุณาเลือกตำแหน่ง</div>
                        </div>
                        <div>
                            <label>ประเภทบุคลากร <span style="color: #dc3545;">*</span></label>
                            <select name="personnel_type" id="personnel_type" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled selected hidden>--เลือกประเภทบุคลากร--</option>
                                <option value="สนับสนุน" {{ old('personnel_type', $user->personnel_type ?? '') == 'สนับสนุน' ? 'selected' : '' }}>สนับสนุน</option>
                                <option value="วิชาการ" {{ old('personnel_type', $user->personnel_type ?? '') == 'วิชาการ' ? 'selected' : '' }}>วิชาการ</option>
                                <option value="บริหาร" {{ old('personnel_type', $user->personnel_type ?? '') == 'บริหาร' ? 'selected' : '' }}>บริหาร</option>
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="personnel_typeError">กรุณาเลือกประเภทบุคลากร</div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลติดต่อ -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลติดต่อ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label>อีเมล <span style="color: #dc3545;">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded px-3 py-2" 
                                placeholder="กรุณากรอกอีเมล"
                                required />
                            <div class="text-red-500 text-sm mt-1 hidden" id="emailError">กรุณากรอกอีเมลให้ถูกต้อง</div>
                        </div>
                        <div>
                            <label>เบอร์โทร <span style="color: #dc3545;">*</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full border rounded px-3 py-2" 
                                placeholder="กรุณากรอกเบอร์โทร"
                                required />
                            <div class="text-red-500 text-sm mt-1 hidden" id="phoneError">กรุณากรอกเบอร์โทรให้ถูกต้อง</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ประวัติการศึกษา</h3>
                    <input type="text" name="bio" id="bio" class="w-full border rounded px-3 py-2" value="{{ old('bio', $user->bio ?? '') }}"/>
                    @error('bio')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- รหัสผ่าน -->
                <div id="passwordPanel">
                    <h3 class="text-purple-600 font-semibold mb-2">รหัสผ่าน <span style="color: #dc3545;">*</span></h3>
                    <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2 placeholder-gray-400" 
                        placeholder="กรุณากรอกรหัสผ่าน"
                        {{ isset($user) ? '' : 'required' }} />
                    <div class="text-red-500 text-sm mt-1 hidden" id="passwordError">กรุณากรอกรหัสผ่านให้ถูกต้อง</div>
                </div>

                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ตั้งค่าผู้ใช้งาน</h3>
                    <div class="mb-3">
                        <label>สถานะ <span style="color: #dc3545;">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active" {{ old('status', $user->status ?? '') == 'active' ? 'selected' : '' }}>active</option>
                            <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>บทบาท (Role) <span style="color: #dc3545;">*</span></label>
                        <select name="role" id="role" class="w-full border rounded px-3 py-2" required>
                            <option value="" disabled selected hidden>--เลือกบทบาท--</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('role', isset($user) && $user ? ($user->roles->first()->name ?? '') : '') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="currentRoleDisplay" class="mt-2 text-sm text-green-700">
                            @php
                                $currentRole = old('role', isset($user) && $user ? ($user->roles->first()->name ?? '') : '');
                            @endphp
                            @if($currentRole)
                                <span>บทบาทที่บันทึกไว้: <strong>{{ $currentRole }}</strong></span>
                            @else
                                <span>ยังไม่ได้เลือกบทบาท</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-center gap-4 mt-8">
                <x-button 
                    type= defualt 
                    text="ย้อนกลับ" 
                    onclick="closeModal()" 
                    icon="fas fa-arrow-left" />
                <x-button 
                    type="primary" 
                    text="บันทึก" 
                    icon="fas fa-save" 
                    buttonType="submit" 
                />
            </div>
        </form>
    </div>
</div>

<script>
// Replace the existing script section in your user modal

function openCreateModal(button) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    form.reset();

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.value = '';
    }

    const action = button.getAttribute('data-action');
    form.action = action;

    document.getElementById('formMethod').value = "POST";

    document.getElementById('passwordPanel').style.display = 'block';
    const passwordInput = document.getElementById('password');
    passwordInput.required = true;
    passwordInput.placeholder = '';

    const modalTitle = modal.querySelector('h2');
    if (modalTitle) {
        modalTitle.textContent = 'เพิ่มเจ้าหน้าที่ใหม่';
    }

    // Clear current user ID
    form.removeAttribute('data-user-id');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openEditModal(user) {
    console.log('Opening edit modal with user data:', user);
    
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    form.action = `/users/${user.id}`;
    document.getElementById('formMethod').value = "PUT";

    // Store current user ID for validation
    form.setAttribute('data-user-id', user.id);

    const modalTitle = modal.querySelector('h2');
    if (modalTitle) {
        modalTitle.textContent = 'แก้ไขข้อมูลเจ้าหน้าที่';
    }

    const textFields = ['name', 'employee_id', 'email', 'phone', 'bio'];
    textFields.forEach(field => {
        const element = document.getElementById(field);
        if (element && user[field] !== undefined) {
            element.value = user[field] || '';
        }
    });

    const selectFields = [
        { id: 'prefix', value: user.prefix },
        { id: 'department_id', value: user.department_id },
        { id: 'position_id', value: user.position_id },
        { id: 'personnel_type', value: user.personnel_type },
        { id: 'status', value: user.status }
    ];

    selectFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element && field.value !== undefined) {
            element.value = field.value || '';
            element.dispatchEvent(new Event('change'));
        }
    });

    document.getElementById('passwordPanel').style.display = 'none';

    const passwordInput = document.getElementById('password');
    passwordInput.required = false;
    passwordInput.value = '';

    const roleSelect = document.getElementById('role');
    if (roleSelect && user.roles && user.roles.length > 0) {
        roleSelect.value = user.roles[0].name;
        roleSelect.dispatchEvent(new Event('change'));
    } else if (roleSelect) {
        roleSelect.value = '';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('userModal');
    const modalTitle = modal.querySelector('h2');
    const form = document.getElementById('userForm');
    
    if (modalTitle) {
        modalTitle.textContent = 'เพิ่มเจ้าหน้าที่ใหม่';
    }
    
    form.reset();
    form.removeAttribute('data-user-id');
    
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.required = true;
        passwordInput.placeholder = '';
    }
    
    // Clear all error messages
    document.querySelectorAll('[id$="Error"]').forEach(err => err.classList.add('hidden'));
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModal').classList.add('flex');
}

// Role selection display
const roleSelect = document.getElementById('role');
const currentRoleDisplay = document.getElementById('currentRoleDisplay');
if (roleSelect && currentRoleDisplay) {
    roleSelect.addEventListener('change', function() {
        if (roleSelect.value) {
            currentRoleDisplay.innerHTML = 'บทบาทที่เลือก: <strong>' + roleSelect.value + '</strong>';
        } else {
            currentRoleDisplay.innerHTML = 'ยังไม่ได้เลือกบทบาท';
        }
    });
}

function showError(id, message) {
    const errorDiv = document.getElementById(id + 'Error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }
    
    const input = document.getElementById(id);
    if (input) {
        input.classList.add('border-red-500');
    }
}

function hideError(id) {
    const errorDiv = document.getElementById(id + 'Error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
    
    const input = document.getElementById(id);
    if (input) {
        input.classList.remove('border-red-500');
    }
}

// Debounce function to prevent excessive API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Check if value is unique via AJAX
async function checkUnique(field, value) {
    const form = document.getElementById('userForm');
    const userId = form.getAttribute('data-user-id'); // Get current user ID when editing
    
    if (!value || value.trim() === '') {
        return { unique: true };
    }

    try {
        const response = await fetch('/users/check-unique', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                field: field,
                value: value,
                user_id: userId // Pass user ID to exclude from check when editing
            })
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error checking uniqueness:', error);
        return { unique: true }; // Fail gracefully
    }
}

// Validate unique fields with debouncing
const validateUniqueField = debounce(async (id, fieldName, label) => {
    const input = document.getElementById(id);
    const value = input.value.trim();

    if (!value) {
        hideError(id);
        return;
    }

    // Show loading state
    const errorDiv = document.getElementById(id + 'Error');
    if (errorDiv) {
        errorDiv.textContent = 'กำลังตรวจสอบ...';
        errorDiv.classList.remove('hidden', 'text-red-500');
        errorDiv.classList.add('text-blue-500');
    }

    const result = await checkUnique(fieldName, value);

    if (!result.unique) {
        errorDiv.classList.remove('text-blue-500');
        errorDiv.classList.add('text-red-500');
        showError(id, `${label}นี้ถูกใช้งานแล้ว`);
    } else {
        hideError(id);
    }
}, 500); // Wait 500ms after user stops typing

function validateField(id, type = 'text') {
    const input = document.getElementById(id);
    if (!input) return;

    const eventType = (type === 'select') ? 'change' : 'input';
    input.addEventListener(eventType, async () => {
        const value = input.value.trim();

        // General required check
        if (!value) {
            showError(id, 'จำเป็นต้องกรอกข้อมูล');
            return;
        }

        // Email format check
        if (id === 'email') {
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!pattern.test(value)) {
                showError(id, 'รูปแบบอีเมลไม่ถูกต้อง');
                return;
            }
            // Check uniqueness
            validateUniqueField(id, 'email', 'อีเมล');
            return;
        }

        // Phone validation
        if (id === 'phone') {
            const digits = value.replace(/\D/g, '');
            if (digits.length !== 10) {
                showError(id, 'กรุณากรอกเบอร์โทร 10 หลัก');
                return;
            }
            // Check uniqueness
            validateUniqueField(id, 'phone', 'เบอร์โทร');
            return;
        }

        // Employee ID uniqueness check
        if (id === 'employee_id') {
            const digitsOnly = /^[0-9]+$/;
            if (!digitsOnly.test(value)) {
                showError(id, 'กรุณากรอกเฉพาะตัวเลขเท่านั้น');
                return;
            }
            validateUniqueField(id, 'employee_id', 'รหัสพนักงาน');
            return;
        }

        // Password length check
        if (id === 'password' && input.required && value.length < 6) {
            showError(id, 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
            return;
        }

        hideError(id);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const requiredTextFields = ['name', 'employee_id', 'email', 'phone'];
    const requiredSelectFields = ['prefix', 'department_id', 'position_id', 'personnel_type', 'status'];

    requiredTextFields.forEach(id => validateField(id, 'text'));
    requiredSelectFields.forEach(id => validateField(id, 'select'));

    document.getElementById('userForm').addEventListener('submit', async function (e) {
        e.preventDefault(); // Prevent immediate submission
        
        let hasError = false;

        // Check all required fields
        [...requiredTextFields, ...requiredSelectFields].forEach(id => {
            const input = document.getElementById(id);
            if (input && !input.value.trim()) {
                showError(id, 'จำเป็นต้องกรอกข้อมูล');
                hasError = true;
            }

            // Email format check
            if (id === 'email' && input && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
                showError(id, 'รูปแบบอีเมลไม่ถูกต้อง');
                hasError = true;
            }

            // Phone format check
            if (id === 'phone' && input) {
                const digits = input.value.replace(/\D/g, '');
                if (digits.length !== 10) {
                    showError(id, 'กรุณากรอกเบอร์โทรให้ถูกต้อง');
                    hasError = true;
                }
            }
        });

        if (hasError) {
            return; // Stop if basic validation fails
        }

        // Check uniqueness for all three fields
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

        if (hasError) {
            return; // Stop submission if uniqueness check fails
        }

        // If all checks pass, submit the form
        this.submit();
    });
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function (e) {
    let digits = this.value.replace(/\D/g, '');

    if (digits.length > 10) {
        digits = digits.slice(0, 10);
    }

    let formatted = digits;
    if (digits.length > 6) {
        formatted = `${digits.slice(0,3)}-${digits.slice(3,6)}-${digits.slice(6)}`;
    } else if (digits.length > 3) {
        formatted = `${digits.slice(0,3)}-${digits.slice(3)}`;
    }

    this.value = formatted;
});
</script>


<!-- Safe overrides to prevent encoding-related JS errors and align form behavior -->
<script>
(function() {
  function setModalTitle(modal, title) {
    const modalTitle = modal.querySelector('h2');
    if (modalTitle) modalTitle.textContent = title;
  }

  window.openCreateModal = function(button) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    form.reset();

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
      roleSelect.value = '';
    }

    const action = button.getAttribute('data-action');
    form.action = action || form.action;

    document.getElementById('formMethod').value = 'POST';

    // Ensure uniqueness checks don't treat current record as different
    // Clear any previously set user id when switching to create mode
    form.removeAttribute('data-user-id');

    const panel = document.getElementById('passwordPanel');
    if (panel) panel.style.display = 'block';
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
      passwordInput.required = true;
      passwordInput.placeholder = '';
      passwordInput.value = '';
    }

    setModalTitle(modal, 'Create User');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  window.openEditModal = function(user) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    form.action = `/users/${user.id}`;
    document.getElementById('formMethod').value = 'PUT';

    // Important: set current user id so unique checks ignore this record
    form.setAttribute('data-user-id', user.id);

    setModalTitle(modal, 'Edit User');

    const textFields = ['name', 'employee_id', 'email', 'phone', 'bio'];
    textFields.forEach(field => {
      const el = document.getElementById(field);
      if (el && user[field] !== undefined) el.value = user[field] || '';
    });

    const selects = [
      { id: 'prefix', value: user.prefix },
      { id: 'department_id', value: user.department_id },
      { id: 'position_id', value: user.position_id },
      { id: 'personnel_type', value: user.personnel_type },
      { id: 'status', value: user.status }
    ];
    selects.forEach(s => {
      const el = document.getElementById(s.id);
      if (el && s.value !== undefined) {
        el.value = s.value || '';
        el.dispatchEvent(new Event('change'));
      }
    });

    const panel = document.getElementById('passwordPanel');
    if (panel) panel.style.display = 'none';
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
      passwordInput.required = false;
      passwordInput.value = '';
    }

    const roleSelect = document.getElementById('role');
    if (roleSelect) {
      if (user.roles && user.roles.length > 0) {
        roleSelect.value = user.roles[0].name;
        roleSelect.dispatchEvent(new Event('change'));
      } else {
        roleSelect.value = '';
      }
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  window.closeModal = function() {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    setModalTitle(modal, 'Create User');
    form.reset();

    // Clear stored user id when closing
    form.removeAttribute('data-user-id');

    const passwordInput = document.getElementById('password');
    if (passwordInput) {
      passwordInput.required = true;
      passwordInput.placeholder = '';
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  };

  document.addEventListener('DOMContentLoaded', function() {
    // Make role optional to match backend validation
    const roleSelect = document.getElementById('role');
    if (roleSelect) roleSelect.removeAttribute('required');

    // Auto-open modal if server validation has errors
    if ({{ $errors->any() ? 'true' : 'false' }}) {
      const modal = document.getElementById('userModal');
      if (modal) {
        setModalTitle(modal, 'Create User');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }
    }
  });
})();
</script>
