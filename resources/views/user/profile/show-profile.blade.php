@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">User Profile</h2>

    <!-- Profile Name and Email -->
    <div class="mb-6">
        <h3 class="text-xl font-bold">{{ $user->name }}</h3>
        <p class="text-sm text-gray-600">{{ $user->email }}</p>
        {{-- @if($user->email_verified_at)
            <p class="text-sm text-green-600">Verified at: {{ $user->email_verified_at->format('d M Y, H:i') }}</p>
        @else
            <p class="text-sm text-red-600">Email not verified</p>
        @endif --}}
    </div>

    <!-- User Info -->
    <div class="grid grid-cols-2 gap-6">
        <div>
            <strong>รหัสพนักงาน:</strong>
            <p class="text-gray-700">{{ $user->employee_id ?? '-' }}</p>
        </div>

        <div>
            <strong>เบอร์โทร:</strong>
            <p class="text-gray-700">
                {{ $user->phone ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $user->phone) : '-' }}
            </p>
        </div>

        <div>
            <strong>ประเภทบุคลากร:</strong>
            <p class="text-gray-700">{{ $user->personnel_type ?? '-' }}</p>
        </div>
        <div>
            <strong>ตำแหน่ง:</strong>
            <p class="text-gray-700">{{ $user->position->name ?? '-' }}</p>
        </div>

        <div>
            <strong>สาขาวิชา:</strong>
            <p class="text-gray-700">{{ $user->department->department_name ?? '-' }}</p>
        </div>

        <div>
            <strong>บทบาท:</strong>
            <p class="text-gray-700">
                {{ $user->roles->pluck('name')->join(', ') ?: '-' }}
            </p>
        </div>

        <div class="col-span-2">
            <strong>ประวัติการศึกษา:</strong>
            <p class="text-gray-700 whitespace-pre-line">{{ $user->bio ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6">
        <x-button 
            type="warning"
            text="แก้ไขข้อมูล"
            icon="fas fa-edit"
            href="{{ route('profile.edit') }}" />
    </div>
</div>
@endsection
