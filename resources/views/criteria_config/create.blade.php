@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\criteria_config\create.blade.php --}}

@section('content')
    {{-- บล็อกเนื้อหา --}}
    <div class="py-12 bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen">
        {{-- บล็อกเนื้อหา --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('criteria_config.partials.page-intro', [
                'title' => 'สร้างเกณฑ์การประเมินใหม่',
                'description' => 'กรุณากรอกข้อมูลเกณฑ์การประเมินให้ครบถ้วนเพื่อสร้างเกณฑ์ที่สมบูรณ์',
            ])

            {{-- ฟอร์ม --}}
            <form id="jsonForm" action="{{ route('report-structure.store') }}" method="POST" class="space-y-8" novalidate>
                @csrf

                @include('criteria_config.partials.create-report-data-section')

                @include('criteria_config.partials.create-categories-section')

                @include('criteria_config.partials.form-actions', [
                    'mode' => 'create',
                    'submitLabel' => 'บันทึกข้อมูล',
                ])
            </form>
        </div>
    </div>

    @include('criteria_config.partials.loading-overlay', [
        'backdropClass' => 'bg-opacity-50',
        'message' => 'กำลังส่งข้อมูล กรุณารอสักครู่...',
    ])
    @include('criteria_config.partials.confirm-modal', [
        'backdropClass' => 'bg-opacity-50',
    ])
    @include('criteria_config.partials.success-modal', [
        'backdropClass' => 'bg-opacity-50',
        'title' => 'ส่งข้อมูลสำเร็จ',
        'message' => 'ข้อมูลเกณฑ์การประเมินถูกบันทึกเรียบร้อยแล้ว',
        'countdown' => 5,
        'countdownPrefix' => 'กำลังเปลี่ยนเส้นทางใน',
        'countdownSuffix' => 'วินาที...',
        'buttonLabel' => 'ไปหน้ารายการเกณฑ์',
    ])
    @include('criteria_config.partials.floating-save-button')
@endsection

@push('scripts')
    @include('criteria_config.partials.create-script')
@endpush
