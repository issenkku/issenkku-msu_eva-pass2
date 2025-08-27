@extends('layouts.app')

@php
    $personnelTypes = [
        'สนับสนุน' => 'สนับสนุน',
        'วิชาการ' => 'วิชาการ',
    ];
@endphp

@section('content')
    <div class="container-fluid">
        <x-header 
            title="จัดการข้อมูลเจ้าหน้าที่" 
            text="ระบบจัดการข้อมูลเจ้าหน้าที่และพนักงาน" 
            icon="fas fa-users" />
    </div>

    <div class="d-flex flex-column flex-md-row justify-between items-start md:items-center mb-4 gap-3">
        <h2 class="text-xl font-bold">รายชื่อเจ้าหน้าที่ทั้งหมด ({{ $users->total() }} คน)</h2>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <x-button 
                type="secondary" 
                text="เพิ่มไฟล์เจ้าหน้าที่" 
                onclick="openImportModal(this)" 
                data-action="{{ route('users.import') }}"
                icon="fas fa-file-import" />

            <x-button 
                type="primary" 
                text="เพิ่มเจ้าหน้าที่" 
                onclick="openCreateModal(this)" 
                data-action="{{ route('users.store') }}"
                icon="fas fa-user-plus" />
        </div>
        @include('user.management.user-form-modal')
        @include('user.management.import-user-modal')
    </div>

    <div class="flex flex-wrap gap-4 mb-4 justify-between">
        <form method="GET" class="flex flex-wrap gap-4 mb-4 items-end">
            <div class="flex items-center gap-2">
                <x-button 
                    type="primary" 
                    text="ทั้งหมด"
                    href="{{ route('users.index') }}" />
            </div>
            <x-filter
                name="department_id"
                label="หน่วยงาน"
                :options="$departments->pluck('department_name', 'id')->toArray()"
            />

            <x-filter
                name="personnel_type"
                label="ประเภทเจ้าหน้าที่"
                :options="$personnelTypes"
            />
            
            <x-filter
                name="position_id"
                label="ตำแหน่งงาน"
                :options="$positions->pluck('name', 'id')->toArray()"
            />
        </form>
        <x-search-bar 
            placeholder="ค้นหาชื่อ, รหัสพนักงาน..."
        /> <!-- <<<< เรียกใช้งาน Component -->
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-4 text-center">ลำดับ</th>
                    <th class="p-4 text-left">ข้อมูลพนักงาน</th>
                    <th class="p-4 text-center">รหัสพนักงาน</th>
                    <th class="p-4 text-center">ตำแหน่งงาน</th>
                    <th class="p-4 text-center">ประเภท</th>
                    <th class="p-4 text-center">ติดต่อ</th>
                    <th class="p-4 text-center">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $index => $user)
                    <x-user-table :index="$index + 1" :employee="[
                        'id' => $user->id,
                        'prefix' => $user->prefix,
                        'name' => $user->name,
                        'code' => $user->employee_id,
                        'position' => optional($user->position)->name,
                        'type' => $user->personnel_type,
                        'contact' => $user->phone,
                        'email' => $user->email,
                        'bio' => $user->bio,
                        'status' => $user->status,
                        'position_id' => $user->position_id,
                        'department_id' => $user->department_id,
                        'role' => $user->getRoleNames()->first(),
                    ]" />
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <i class="fas fa-user text-3xl mb-2 block"></i>
                            <p>ไม่มีข้อมูลเจ้าหน้าที่</p>
                        </td>
                    </tr>
                @endforelse
                
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-4">
        <span>แสดง {{ $users->firstItem() }} - {{ $users->lastItem() }} จาก {{ $users->total() }} รายการ</span>
        <div class="flex gap-2 items-center">
            {{ $users->links() }}
        </div>
    </div>
@endsection

