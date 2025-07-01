@extends('layouts.user-management-page')

@php
    $personnelTypes = [
        'สนับสนุน' => 'สนับสนุน',
        'วิชาการ' => 'วิชาการ',
    ];
@endphp

@section('content')
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">รายชื่อเจ้าหน้าที่ทั้งหมด ({{ count($users) }} คน)</h2>
        <div class="space-x-2">
            <x-button type="primary" text="เพิ่มเจ้าหน้าที่" onclick="openCreateModal(this)" data-action="{{ route('users.store') }}" />
        </div>
        @include('user.management.user-form-modal')
    </div>

    {{-- <div class="flex flex-wrap gap-4 mb-4">
        <x-search-bar />
        <!-- <x-filter label="ตำแหน่ง" name="position_id" :options="$positions" /> -->
        <x-filter label="ประเภทบุคลากร" name="personnel_type_id" :options="$personnelTypes" />
    </div> --}}

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-center">
        <select name="department_id" class="border rounded px-2 py-1">
            <option value="">ทุกแผนก</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                    {{ $department->department_name }}
                </option>
            @endforeach
        </select>
        <select name="position_id" class="border rounded px-2 py-1">
            <option value="">ทุกตำแหน่ง</option>
            @foreach($positions as $position)
                <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                    {{ $position->name }}
                </option>
            @endforeach
        </select>
        <select name="personnel_type" class="border rounded px-2 py-1">
            <option value="">ทุกประเภท</option>
            @foreach($personnelTypes as $type)
                <option value="{{ $type }}" {{ request('personnel_type') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        <select name="status" class="border rounded px-2 py-1">
            <option value="">ทุกสถานะ</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>inactive</option>
        </select>
        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">ค้นหา</button>
        <a href="{{ route('users.index') }}" class="text-gray-500 underline ml-2">ล้าง</a>
    </form>

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
                @foreach ($users as $index => $user)
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
                @endforeach
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

