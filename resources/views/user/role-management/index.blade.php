@extends('layouts.app')
@section('content')
    @include('user.role-management.partials.index-styles')

    {{-- หน้าจัดการบทบาทและสิทธิ์: ส่วนหัว ตาราง modal และ script ถูกแยกตามหน้าที่ --}}
    <div class="p-6">
        @include('user.role-management.partials.index-header')
        @include('user.role-management.partials.index-table')
    </div>

    @include('user.role-management.partials.index-modal')
    @include('user.role-management.partials.index-script')
@endsection
