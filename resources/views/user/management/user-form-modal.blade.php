<!-- Modal Trigger Button (optional) -->
<!-- <x-button text="เพิ่มเจ้าหน้าที่ใหม่" onclick="openModal()" /> -->

<!-- Modal Background -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <!-- Modal Box -->
    <div class="bg-white rounded-xl w-full max-w-3xl p-6 relative">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-3">
            <h2 class="text-lg font-semibold text-purple-700">เพิ่มเจ้าหน้าที่ใหม่</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-red-500">&times;</button>
        </div>

        <!-- Form -->
        <form id="userForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-1 gap-6 mt-4">
                <!-- ข้อมูลส่วนบุคคล -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลส่วนบุคคล</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block">คำนำหน้า</label>
                            <select name="prefix" id="prefix" class="w-full border rounded px-3 py-2" required>
                                <option value="">คำนำหน้า</option>
                                <option value="นาย" {{ old('prefix', $user->prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix', $user->prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix', $user->prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block">ชื่อ-นามสกุล</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border rounded px-3 py-2" required />
                        </div>
                        <div class="md:col-span-3">
                            <label class="block">รหัสพนักงาน</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id ?? '') }}" class="w-full border rounded px-3 py-2" required />
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลงาน -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลงาน</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label>สาขาวิชา</label>
                            <select name="department_id" id="department_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">เลือกสาขาวิชา</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>ตำแหน่ง</label>
                            <select name="position_id" id="position_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">เลือกตำแหน่ง</option>
                                @foreach ($positions as $position) 
                                    <option value="{{ $position->id }}"
                                        {{ old('position_id', $user->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>ประเภทบุคลากร</label>
                            <select name="personnel_type" id="personnel_type" class="w-full border rounded px-3 py-2" required>
                                <option value="">เลือกประเภทบุคลากร</option>
                                <option value="สนับสนุน" {{ old('personnel_type', $user->personnel_type ?? '') == 'สนับสนุน' ? 'selected' : '' }}>สนับสนุน</option>
                                <option value="วิชาการ" {{ old('personnel_type', $user->personnel_type ?? '') == 'วิชาการ' ? 'selected' : '' }}>วิชาการ</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลติดต่อ -->
                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ข้อมูลติดต่อ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label>อีเมล</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded px-3 py-2" required />
                        </div>
                        <div>
                            <label>เบอร์โทร</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full border rounded px-3 py-2" required />
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ประวัติการศึกษา</h3>
                    <input type="text" name="bio" id="bio" class="w-full border rounded px-3 py-2" value="{{ old('bio', $user->bio ?? '') }}"/>
                </div>

                <!-- รหัสผ่าน -->
                <div id="passwordPanel">
                    <h3 class="text-purple-600 font-semibold mb-2">รหัสผ่าน</h3>
                    <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2" {{ isset($user) ? '' : 'required' }} />
                </div>

                <div>
                    <h3 class="text-purple-600 font-semibold mb-2">ตั้งค่าผู้ใช้งาน</h3>
                    <div>
                        <label>สถานะ</label>
                        <select name="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active" {{ old('status', $user->status ?? '') == 'active' ? 'selected' : '' }}>active</option>
                            <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-center gap-4 mt-8">
                <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-800 px-6 py-2 rounded hover:bg-gray-400">
                    ยกเลิก
                </button>
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal(button) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    // Reset the form
    form.reset();

    // Reset role dropdown
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.value = '';
    }

    // Use route from data attribute
    const action = button.getAttribute('data-action');
    form.action = action;

    // Set form method to POST
    document.getElementById('formMethod').value = "POST";

    document.getElementById('passwordPanel').style.display = 'block';
    const passwordInput = document.getElementById('password');
    passwordInput.required = true;
    passwordInput.placeholder = '';

    // Reset modal title
    const modalTitle = modal.querySelector('h2');
    if (modalTitle) {
        modalTitle.textContent = 'เพิ่มเจ้าหน้าที่ใหม่';
    }

    // Show the modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openEditModal(user) {
    console.log('Opening edit modal with user data:', user); // Debug log
    
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    // Set form action to update route
    form.action = `/users/${user.id}`;
    document.getElementById('formMethod').value = "PUT";

    // Update modal title
    const modalTitle = modal.querySelector('h2');
    if (modalTitle) {
        modalTitle.textContent = 'แก้ไขข้อมูลเจ้าหน้าที่';
    }

    // Populate text inputs
    const textFields = ['name', 'employee_id', 'email', 'phone', 'bio'];
    textFields.forEach(field => {
        const element = document.getElementById(field);
        if (element && user[field] !== undefined) {
            element.value = user[field] || '';
            console.log(`Set ${field} to:`, user[field]); // Debug log
        }
    });

    // Populate select dropdowns
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
            console.log(`Set ${field.id} to:`, field.value); // Debug log
            
            // Trigger change event in case there are dependent dropdowns
            element.dispatchEvent(new Event('change'));
        }
    });

    document.getElementById('passwordPanel').style.display = 'none';

    const passwordInput = document.getElementById('password');
    passwordInput.required = false;
    passwordInput.value = '';
    passwordInput.placeholder = 'เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน';

    const roleSelect = document.getElementById('role');
    if (roleSelect && user.role) {
        roleSelect.value = user.role;
        roleSelect.dispatchEvent(new Event('change'));
    }

    // Show the modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('userModal');
    const modalTitle = modal.querySelector('h2');
    const form = document.getElementById('userForm');
    
    // Reset modal title
    if (modalTitle) {
        modalTitle.textContent = 'เพิ่มเจ้าหน้าที่ใหม่';
    }
    
    // Reset form
    form.reset();
    
    // Reset password requirement
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.required = true;
        passwordInput.placeholder = '';
    }
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModal').classList.add('flex');
}
</script>

