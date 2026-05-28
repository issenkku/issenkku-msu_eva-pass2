@extends('layouts.app')
@section('content')
    {{-- หน้าแก้ไขบทบาท: ส่วนหัว ฟอร์ม และปุ่มท้ายฟอร์มถูกแยกตามหน้าที่ --}}
    <div class="p-6 max-w-3xl mx-auto">
        @include('user.role-management.partials.edit-role-header')

        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')

            @include('user.role-management.partials.edit-role-form')
            @include('user.role-management.partials.edit-role-actions')
        </form>
    </div>
@endsection
