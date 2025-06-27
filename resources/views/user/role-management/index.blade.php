@extends('layouts.admin')

@section('title', 'การจัดการบทบาทและสิทธิ์การเข้าถึง')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gray-800 text-white px-6 py-4">
        <h1 class="text-xl font-semibold">การจัดการบทบาทและสิทธิ์การเข้าถึง</h1>
    </div>

    <div class="p-6">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button class="tab-button active border-b-2 border-purple-500 py-2 px-1 text-purple-600 font-medium text-sm" data-tab="roles">
                        บทบาท (Roles)
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700 font-medium text-sm" data-tab="permissions">
                        สิทธิ์ (Permissions)
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700 font-medium text-sm" data-tab="assignments">
                        กำหนดสิทธิ์ผู้ใช้
                    </button>
                </nav>
            </div>
        </div>

        <!-- Roles Tab -->
        <div id="roles-tab" class="tab-content">
            <div class="bg-white rounded-lg shadow">
                <!-- Header with Add Button -->
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">รายชื่อบทบาททั้งหมด ({{ $roles->count() ?? 0 }} บทบาท)</h2>
                    </div>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="openRoleModal()">
                        เพิ่มบทบาทใหม่
                    </button>
                </div>

                <!-- Search -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input type="text" id="roleSearch" placeholder="ค้นหาบทบาท..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <select id="roleStatusFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            <option value="">สถานะทั้งหมด</option>
                            <option value="active">ใช้งาน</option>
                            <option value="inactive">ไม่ใช้งาน</option>
                        </select>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อบทบาท</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รหัสบทบาท</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำอธิบาย</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนผู้ใช้</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($roles ?? [] as $index => $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $role->display_name ?? $role->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $role->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $role->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($role->is_active ?? true)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            ใช้งาน
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            ไม่ใช้งาน
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $role->users_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs" 
                                            onclick="editRole({{ $role->id }})">
                                        แก้ไข
                                    </button>
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs"
                                            onclick="managePermissions({{ $role->id }})">
                                        จัดการสิทธิ์
                                    </button>
                                    @if(($role->users_count ?? 0) == 0)
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs" 
                                            onclick="deleteRole({{ $role->id }})">
                                        ลบ
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">ไม่พบข้อมูลบทบาท</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($roles) && $roles->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $roles->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Permissions Tab -->
        <div id="permissions-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow">
                <!-- Header with Add Button -->
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">รายการสิทธิ์ทั้งหมด ({{ $permissions->count() ?? 0 }} สิทธิ์)</h2>
                    </div>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium" onclick="openPermissionModal()">
                        เพิ่มสิทธิ์ใหม่
                    </button>
                </div>

                <!-- Search -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input type="text" id="permissionSearch" placeholder="ค้นหาสิทธิ์..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <select id="permissionGroupFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            <option value="">กลุ่มทั้งหมด</option>
                            <option value="users">ผู้ใช้</option>
                            <option value="content">เนื้อหา</option>
                            <option value="system">ระบบ</option>
                        </select>
                    </div>
                </div>

                <!-- Permissions Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อสิทธิ์</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รหัสสิทธิ์</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">กลุ่ม</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำอธิบาย</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($permissions ?? [] as $index => $permission)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $permission->display_name ?? $permission->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $permission->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $permission->group ?? 'ทั่วไป' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $permission->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs" 
                                            onclick="editPermission({{ $permission->id }})">
                                        แก้ไข
                                    </button>
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs" 
                                            onclick="deletePermission({{ $permission->id }})">
                                        ลบ
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">ไม่พบข้อมูลสิทธิ์</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Assignments Tab -->
        <div id="assignments-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">กำหนดสิทธิ์ผู้ใช้ ({{ $users->count() ?? 0 }} คน)</h2>
                </div>

                <!-- Search -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input type="text" id="userSearch" placeholder="ค้นหาผู้ใช้..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <select id="userRoleFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            <option value="">บทบาททั้งหมด</option>
                            @foreach($roles ?? [] as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อผู้ใช้</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รหัสพนักงาน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อีเมล</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บทบาทปัจจุบัน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users ?? [] as $index => $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->employee_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-1">
                                            {{ $role->display_name ?? $role->name }}
                                        </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">ไม่มีบทบาท</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs" 
                                            onclick="assignRole({{ $user->id }})">
                                        กำหนดบทบาท
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">ไม่พบข้อมูลผู้ใช้</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="mt-6 text-sm text-gray-500">
            แสดง 1 - {{ min(($roles->count() ?? 0), 4) }} จาก {{ $roles->count() ?? 0 }} รายการ
        </div>
    </div>
</div>

<!-- Role Modal -->
<div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900" id="roleModalTitle">เพิ่มบทบาทใหม่</h3>
            <form id="roleForm" class="mt-4 space-y-4">
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">ชื่อบทบาท</label>
                    <input type="text" id="roleDisplayName" name="display_name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">รหัสบทบาท</label>
                    <input type="text" id="roleName" name="name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">คำอธิบาย</label>
                    <textarea id="roleDescription" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                <div class="text-left">
                    <label class="flex items-center">
                        <input type="checkbox" id="roleIsActive" name="is_active" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" checked>
                        <span class="ml-2 text-sm text-gray-700">เปิดใช้งาน</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" onclick="closeRoleModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permission Modal -->
<div id="permissionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900" id="permissionModalTitle">เพิ่มสิทธิ์ใหม่</h3>
            <form id="permissionForm" class="mt-4 space-y-4">
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">ชื่อสิทธิ์</label>
                    <input type="text" id="permissionDisplayName" name="display_name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">รหัสสิทธิ์</label>
                    <input type="text" id="permissionName" name="name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">กลุ่ม</label>
                    <select id="permissionGroup" name="group" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="users">ผู้ใช้</option>
                        <option value="content">เนื้อหา</option>
                        <option value="system">ระบบ</option>
                    </select>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">คำอธิบาย</label>
                    <textarea id="permissionDescription" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" onclick="closePermissionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div id="assignRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900">กำหนดบทบาทผู้ใช้</h3>
            <form id="assignRoleForm" class="mt-4 space-y-4">
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">ผู้ใช้</label>
                    <input type="text" id="assignUserName" readonly class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
                </div>
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700">เลือกบทบาท</label>
                    <div class="mt-2 space-y-2 max-h-48 overflow-y-auto">
                        @foreach($roles ?? [] as $role)
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $role->display_name ?? $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" onclick="closeAssignRoleModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">ยกเลิก</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-purple-500', 'text-purple-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Add active class to clicked button
            button.classList.add('active', 'border-purple-500', 'text-purple-600');
            button.classList.remove('border-transparent', 'text-gray-500');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.remove('hidden');
        });
    });
});

// Role Modal Functions
function openRoleModal(roleId = null) {
    document.getElementById('roleModal').classList.remove('hidden');
    if (roleId) {
        document.getElementById('roleModalTitle').textContent = 'แก้ไขบทบาท';
        // Load role data for editing
    } else {
        document.getElementById('roleModalTitle').textContent = 'เพิ่มบทบาทใหม่';
        document.getElementById('roleForm').reset();
    }
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.add('hidden');
}

function editRole(roleId) {
    openRoleModal(roleId);
}

function deleteRole(roleId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบบทบาทนี้?')) {
        // Handle role deletion
        console.log('Delete role:', roleId);
    }
}

// Permission Modal Functions
function openPermissionModal(permissionId = null) {
    document.getElementById('permissionModal').classList.remove('hidden');
    if (permissionId) {
        document.getElementById('permissionModalTitle').textContent = 'แก้ไขสิทธิ์';
        // Load permission data for editing
        loadPermissionData(permissionId);
    } else {
        document.getElementById('permissionModalTitle').textContent = 'เพิ่มสิทธิ์ใหม่';
        document.getElementById('permissionForm').reset();
    }
}

function closePermissionModal() {
    document.getElementById('permissionModal').classList.add('hidden');
}

function editPermission(permissionId) {
    openPermissionModal(permissionId);
}

function deletePermission(permissionId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบสิทธิ์นี้?')) {
        // Handle permission deletion
        fetch(`/admin/permissions/${permissionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาดในการลบสิทธิ์');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
}

// Assign Role Modal Functions
let currentUserId = null;

function assignRole(userId) {
    currentUserId = userId;
    document.getElementById('assignRoleModal').classList.remove('hidden');
    
    // Get user data and populate modal
    const userRow = document.querySelector(`button[onclick="assignRole(${userId})"]`).closest('tr');
    const userName = userRow.querySelector('td:nth-child(2)').textContent.trim();
    document.getElementById('assignUserName').value = userName;
    
    // Load current user roles
    loadUserRoles(userId);
}

function closeAssignRoleModal() {
    document.getElementById('assignRoleModal').classList.add('hidden');
    currentUserId = null;
}

function loadUserRoles(userId) {
    fetch(`/admin/users/${userId}/roles`)
        .then(response => response.json())
        .then(data => {
            // Clear all checkboxes first
            document.querySelectorAll('#assignRoleForm input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check user's current roles
            if (data.roles) {
                data.roles.forEach(roleId => {
                    const checkbox = document.querySelector(`#assignRoleForm input[value="${roleId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading user roles:', error);
        });
}

// Manage Permissions for Role
function managePermissions(roleId) {
    // This could open another modal or redirect to a dedicated page
    window.location.href = `/admin/roles/${roleId}/permissions`;
}

// Form Submissions
document.addEventListener('DOMContentLoaded', function() {
    // Role Form Submission
    const roleForm = document.getElementById('roleForm');
    if (roleForm) {
        roleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(roleForm);
            const data = Object.fromEntries(formData);
            data.is_active = document.getElementById('roleIsActive').checked;
            
            const url = data.id ? `/admin/roles/${data.id}` : '/admin/roles';
            const method = data.id ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeRoleModal();
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        });
    }

    // Permission Form Submission
    const permissionForm = document.getElementById('permissionForm');
    if (permissionForm) {
        permissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(permissionForm);
            const data = Object.fromEntries(formData);
            
            const url = data.id ? `/admin/permissions/${data.id}` : '/admin/permissions';
            const method = data.id ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closePermissionModal();
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        });
    }

    // Assign Role Form Submission
    const assignRoleForm = document.getElementById('assignRoleForm');
    if (assignRoleForm) {
        assignRoleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedRoles = Array.from(document.querySelectorAll('#assignRoleForm input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);
            
            fetch(`/admin/users/${currentUserId}/roles`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ roles: selectedRoles })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAssignRoleModal();
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        });
    }
});

// Search and Filter Functions
function setupSearchAndFilters() {
    // Role Search
    const roleSearch = document.getElementById('roleSearch');
    const roleStatusFilter = document.getElementById('roleStatusFilter');
    
    if (roleSearch) {
        roleSearch.addEventListener('input', debounce(filterRoles, 300));
    }
    
    if (roleStatusFilter) {
        roleStatusFilter.addEventListener('change', filterRoles);
    }
    
    // Permission Search
    const permissionSearch = document.getElementById('permissionSearch');
    const permissionGroupFilter = document.getElementById('permissionGroupFilter');
    
    if (permissionSearch) {
        permissionSearch.addEventListener('input', debounce(filterPermissions, 300));
    }
    
    if (permissionGroupFilter) {
        permissionGroupFilter.addEventListener('change', filterPermissions);
    }
    
    // User Search
    const userSearch = document.getElementById('userSearch');
    const userRoleFilter = document.getElementById('userRoleFilter');
    
    if (userSearch) {
        userSearch.addEventListener('input', debounce(filterUsers, 300));
    }
    
    if (userRoleFilter) {
        userRoleFilter.addEventListener('change', filterUsers);
    }
}

function filterRoles() {
    const searchTerm = document.getElementById('roleSearch').value.toLowerCase();
    const statusFilter = document.getElementById('roleStatusFilter').value;
    const rows = document.querySelectorAll('#roles-tab tbody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip "no data" row
        
        const roleName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const roleCode = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const statusElement = row.querySelector('td:nth-child(5) span');
        const status = statusElement ? (statusElement.textContent.includes('ใช้งาน') ? 'active' : 'inactive') : 'active';
        
        const matchesSearch = roleName.includes(searchTerm) || roleCode.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function filterPermissions() {
    const searchTerm = document.getElementById('permissionSearch').value.toLowerCase();
    const groupFilter = document.getElementById('permissionGroupFilter').value;
    const rows = document.querySelectorAll('#permissions-tab tbody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip "no data" row
        
        const permissionName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const permissionCode = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const group = row.querySelector('td:nth-child(4) span').textContent.toLowerCase();
        
        const matchesSearch = permissionName.includes(searchTerm) || permissionCode.includes(searchTerm);
        const matchesGroup = !groupFilter || group.includes(groupFilter);
        
        row.style.display = (matchesSearch && matchesGroup) ? '' : 'none';
    });
}

function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const roleFilter = document.getElementById('userRoleFilter').value;
    const rows = document.querySelectorAll('#assignments-tab tbody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip "no data" row
        
        const userName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const userEmail = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
        const employeeId = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        // Check if user has the filtered role
        let hasRole = true;
        if (roleFilter) {
            const roleSpans = row.querySelectorAll('td:nth-child(5) span');
            hasRole = Array.from(roleSpans).some(span => 
                span.textContent.trim() !== 'ไม่มีบทบาท' && 
                span.getAttribute('data-role-id') === roleFilter
            );
        }
        
        const matchesSearch = userName.includes(searchTerm) || 
                            userEmail.includes(searchTerm) || 
                            employeeId.includes(searchTerm);
        
        row.style.display = (matchesSearch && hasRole) ? '' : 'none';
    });
}

// Utility Functions
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

function loadPermissionData(permissionId) {
    fetch(`/admin/permissions/${permissionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.permission) {
                const permission = data.permission;
                document.getElementById('permissionDisplayName').value = permission.display_name || '';
                document.getElementById('permissionName').value = permission.name || '';
                document.getElementById('permissionGroup').value = permission.group || '';
                document.getElementById('permissionDescription').value = permission.description || '';
                
                // Store permission ID for update
                const form = document.getElementById('permissionForm');
                form.setAttribute('data-permission-id', permissionId);
            }
        })
        .catch(error => {
            console.error('Error loading permission data:', error);
        });
}

function loadRoleData(roleId) {
    fetch(`/admin/roles/${roleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.role) {
                const role = data.role;
                document.getElementById('roleDisplayName').value = role.display_name || '';
                document.getElementById('roleName').value = role.name || '';
                document.getElementById('roleDescription').value = role.description || '';
                document.getElementById('roleIsActive').checked = role.is_active !== false;
                
                // Store role ID for update
                const form = document.getElementById('roleForm');
                form.setAttribute('data-role-id', roleId);
            }
        })
        .catch(error => {
            console.error('Error loading role data:', error);
        });
}

// Auto-generate role code from display name
document.addEventListener('DOMContentLoaded', function() {
    const roleDisplayName = document.getElementById('roleDisplayName');
    const roleName = document.getElementById('roleName');
    
    if (roleDisplayName && roleName) {
        roleDisplayName.addEventListener('input', function() {
            // Auto-generate role code if it's empty
            if (!roleName.value) {
                const code = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/\s+/g, '-')     // Replace spaces with hyphens
                    .replace(/-+/g, '-')      // Replace multiple hyphens with single
                    .trim();
                roleName.value = code;
            }
        });
    }
    
    // Auto-generate permission code from display name
    const permissionDisplayName = document.getElementById('permissionDisplayName');
    const permissionName = document.getElementById('permissionName');
    
    if (permissionDisplayName && permissionName) {
        permissionDisplayName.addEventListener('input', function() {
            // Auto-generate permission code if it's empty
            if (!permissionName.value) {
                const code = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/\s+/g, '-')     // Replace spaces with hyphens
                    .replace(/-+/g, '-')      // Replace multiple hyphens with single
                    .trim();
                permissionName.value = code;
            }
        });
    }
    
    // Initialize search and filters
    setupSearchAndFilters();
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const modals = ['roleModal', 'permissionModal', 'assignRoleModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape key to close modals
    if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.fixed:not(.hidden)');
        visibleModals.forEach(modal => {
            modal.classList.add('hidden');
        });
    }
    
    // Ctrl/Cmd + N to add new role when on roles tab
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        const activeTab = document.querySelector('.tab-button.active');
        if (activeTab && activeTab.getAttribute('data-tab') === 'roles') {
            e.preventDefault();
            openRoleModal();
        }
    }
});