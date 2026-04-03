@extends('layouts.app')

@section('content')
    {{-- บล็อกเนื้อหา --}}
    <div class="py-12 bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen">
        {{-- บล็อกเนื้อหา --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('criteria_config.partials.page-intro', [
                'title' => 'แก้ไขเกณฑ์การประเมิน',
                'description' => 'กรุณาแก้ไขข้อมูลเกณฑ์การประเมินตามที่ต้องการ',
            ])

            {{-- ฟอร์ม --}}
            <form id="editForm" action="{{ route('report-structure.update', ['id' => $id ?? '']) }}" method="POST" class="space-y-8" novalidate>
                @csrf
                @method('PUT')

                @include('criteria_config.partials.edit-report-data-section')

                @include('criteria_config.partials.edit-categories-section')

                @include('criteria_config.partials.form-actions', [
                    'mode' => 'edit',
                    'submitLabel' => 'บันทึกการแก้ไข',
                ])
            </form>
        </div>
    </div>

    @include('criteria_config.partials.loading-overlay', [
        'backdropClass' => 'bg-gray-900 bg-opacity-50',
        'message' => 'กำลังโหลดข้อมูล กรุณารอสักครู่...',
    ])

    @include('criteria_config.partials.success-modal', [
        'backdropClass' => 'bg-gray-900 bg-opacity-50',
        'title' => 'บันทึกสำเร็จ',
        'message' => 'ข้อมูลเกณฑ์การประเมินถูกแก้ไขเรียบร้อยแล้ว',
        'buttonLabel' => 'กลับหน้าหลัก',
    ])

    @include('criteria_config.partials.floating-save-button')
@endsection

@push('scripts')
    @include('criteria_config.partials.edit-script')
@endpush
