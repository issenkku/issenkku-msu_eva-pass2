@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/user/management/log.blade.php --}}

@section('content')
    {{-- โครงหน้าประวัติการใช้งาน --}}
    <div class="container-fluid">
        <x-header
            title="บันทึกประวัติการเข้าใช้งาน"
            text="ระบบบันทึกประวัติการเข้าใช้งานของผู้ใช้"
            icon="fas fa-history"
        />

        @include('user.management.partials.log-filter-section')
        @include('user.management.partials.log-table-section')
    </div>

    @include('user.management.partials.log-activity-modals')
    @include('user.management.partials.log-styles')
@endsection
