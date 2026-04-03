{{-- ไฟล์มุมมอง: resources/views\criteria_config\index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ส่วนหัวหน้าและ grid ถูกแยกเป็น partial เพื่อให้หน้าแม่เหลือเฉพาะ composition --}}
            @include('criteria_config.partials.index-page-header')
            @include('criteria_config.partials.index-grid-loading')
        </div>
    </div>

    {{-- script ของหน้า index ถูกย้ายออกทั้งก้อนแล้ว และ include จากที่เดียว --}}
    @include('criteria_config.partials.index-script')
@endsection
