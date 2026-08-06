@extends('layouts.app')
@section('title', 'จัดการข้อมูลรายวิชา')

@section('content')
    @include('subjects.partials.index-styles')

    <div class="container-fluid">
        <x-header
            title="จัดการข้อมูลรายวิชา"
            text="ระบบจัดการข้อมูลรายวิชา"
            icon="fas fa-book" />

        @include('subjects.partials.import-result', ['importResult' => $importResult ?? null])

        <div class="d-flex justify-content-end mb-3 gap-2 flex-wrap">
            <x-button
                type="danger"
                buttonType="button"
                text="ลบรายการที่เลือก"
                class="hidden"
                data-bulk-delete-open
                icon="fas fa-trash-alt" />
            <x-button
                type="secondary"
                buttonType="button"
                text="นำเข้าจาก Excel"
                icon="fas fa-file-import"
                data-subject-import-open />
            <x-button
                type="primary"
                buttonType="button"
                text="เพิ่มรายวิชา"
                data-create-modal-open
                icon="fas fa-plus" />
        </div>

        <x-list-toolbar
            searchPlaceholder="ค้นหารหัสหรือชื่อรายวิชา..."
            :sortOptions="[
                'manual' => 'จัดอันดับเอง',
                'code_asc' => 'รหัส A-Z',
                'code_desc' => 'รหัส Z-A',
                'name_asc' => 'ชื่อ A-Z',
                'name_desc' => 'ชื่อ Z-A',
                'latest' => 'ใหม่สุด',
                'oldest' => 'เก่าสุด',
            ]"
            :filters="[
                [
                    'name' => 'status',
                    'label' => 'สถานะ',
                    'options' => [
                        'active' => 'เปิดใช้งาน',
                        'inactive' => 'ปิดใช้งาน',
                    ],
                    'placeholder' => 'ทั้งหมด',
                ],
                [
                    'name' => 'per_page',
                    'label' => 'จำนวนรายการต่อหน้า',
                    'options' => [
                        10 => '10 รายการ',
                        25 => '25 รายการ',
                        50 => '50 รายการ',
                        100 => '100 รายการ',
                    ],
                    'value' => request('per_page', 10),
                ],
            ]"
        />

        @include('subjects.partials.index-table-section')
    </div>

    <x-subject-modal />
    @include('subjects.partials.import-modal')
    @include('subjects.imports.preview-modal', [
        'importPreview' => $importPreview ?? null,
        'importPreviewToken' => $importPreviewToken ?? null,
    ])

    <x-delete-warning-modal
        :async="true"
        text="รายวิชา"
        formAction="{{ route('subjects.destroy', ':id') }}"
        entityUrl="/subjects" />

    <x-bulk-delete-modal
        :async="true"
        modalId="bulkDeleteSubjectsModal"
        title="ยืนยันการลบรายวิชา"
        entityText="รายวิชา"
        formAction="{{ route('subjects.bulk-destroy') }}" />

    @include('subjects.partials.index-flash-message')
    @include('subjects.partials.import-modal-script')
    @include('subjects.partials.index-script')
    @include('partials.table-reorder-script')
@endsection
