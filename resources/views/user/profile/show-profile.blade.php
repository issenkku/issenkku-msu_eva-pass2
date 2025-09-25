@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">User Profile</h2>

    <!-- Profile Photo and Name Section -->
    <div class="flex items-center mb-6 gap-6">
        <div class="flex-shrink-0">
            <img src="{{ $user->profile_photo_url }}" 
                 alt="Profile Photo"
                 class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg">
        </div>
        <div class="flex-grow">
            <h3 class="text-2xl font-bold text-gray-800">{{ $user->prefix }} {{ $user->name }}</h3>
            <p class="text-lg text-gray-600">{{ $user->email }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $user->position->name ?? '-' }} | {{ $user->department->department_name ?? '-' }}</p>
        </div>
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
            <strong>บทบาท:</strong>
            <p class="text-gray-700">
                {{ $user->roles->pluck('name')->join(', ') ?: '-' }}
            </p>
        </div>

        <div class="col-span-2">
            <strong>ประวัติการศึกษา:</strong>
            <p class="text-gray-700 whitespace-pre-line">{{ $user->bio ?? '-' }}</p>
        </div>

        @if($user->portfolio)
        <div class="col-span-2">
            <strong>ผลงาน:</strong>
            <p class="text-gray-700 whitespace-pre-line">{{ $user->portfolio }}</p>
        </div>
        @endif
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
