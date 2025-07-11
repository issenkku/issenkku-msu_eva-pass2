@extends('layouts.app')

@php
    $personnelTypes = [
        'สนับสนุน' => 'สนับสนุน',
        'วิชาการ' => 'วิชาการ',
    ];
@endphp

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">รายชื่อเจ้าหน้าที่ทั้งหมด ({{ count($users) }} คน)</h2>
        <div class="space-x-2">
            <x-button type="primary" text="เพิ่มเจ้าหน้าที่" onclick="openCreateModal(this)" data-action="{{ route('users.store') }}" />
        </div>
        @include('user.management.user-form-modal')
    </div>

    <div class="flex flex-wrap gap-4 mb-4">
        <x-search-bar /> <!-- <<<< เรียกใช้งาน Component -->
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

