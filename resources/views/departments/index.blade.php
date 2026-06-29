@extends('layouts.app')
@section('title', 'จัดการข้อมูลแผนก')

@section('content')
    @include('departments.partials.index-styles')

    <div class="container-fluid">
        <x-header
            title="จัดการข้อมูลแผนก"
            text="ระบบจัดการข้อมูลแผนกและคณะ"
            icon="fas fa-building" />

        <div class="d-flex justify-content-end mb-3 gap-2 flex-wrap">
            <x-button
                type="danger"
                buttonType="button"
                text="ลบรายการที่เลือก"
                class="hidden"
                data-bulk-delete-open
                icon="fas fa-trash-alt" />
            <x-button
                type="primary"
                buttonType="button"
                text="เพิ่มแผนก"
                data-create-modal-open
                icon="fas fa-plus" />
        </div>

        <x-list-toolbar
            searchPlaceholder="ค้นหาชื่อแผนก..."
            :sortOptions="[
                'manual' => 'จัดอันดับเอง',
                'latest' => 'ใหม่สุด',
                'oldest' => 'เก่าสุด',
                'name_asc' => 'ชื่อ A-Z',
                'name_desc' => 'ชื่อ Z-A',
                'most_users' => 'ใช้งานมากที่สุด',
                'least_users' => 'ใช้งานน้อยที่สุด',
            ]"
            :filters="[
                [
                    'name' => 'usage',
                    'label' => 'สถานะการใช้งาน',
                    'options' => [
                        'used' => 'มีบุคลากรใช้งาน',
                        'unused' => 'ยังไม่มีบุคลากร',
                    ],
                    'placeholder' => 'ทั้งหมด',
                ],
            ]"
        />

        @include('departments.partials.index-table-section')
    </div>

    @include('departments.partials.index-modal')

    <x-delete-warning-modal
        text="แผนก"
        formAction="{{ route('departments.destroy', ':id') }}"
        entityUrl="/departments" />

    <x-bulk-delete-modal
        modalId="bulkDeleteDepartmentsModal"
        title="ยืนยันการลบแผนก"
        entityText="แผนก"
        formAction="{{ route('departments.bulk-destroy') }}" />

    @include('departments.partials.index-flash-message')
    @include('departments.partials.index-script')
    @include('partials.table-reorder-script')
@endsection
