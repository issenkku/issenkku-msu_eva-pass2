{{-- ไฟล์มุมมอง: resources/views/positions/index.blade.php --}}
@extends('layouts.app')
@section('title', 'จัดการข้อมูลตำแหน่ง')

@section('content')
    @include('positions.partials.index-styles')

    <div class="container-fluid">
        <x-header
            title="จัดการข้อมูลตำแหน่ง"
            text="ระบบจัดการข้อมูลตำแหน่งงาน"
            icon="fas fa-user-tie" />

        <div class="d-flex justify-content-end mb-3">
            <x-button
                type="primary"
                buttonType="button"
                text="เพิ่มตำแหน่ง"
                onclick="openCreateModal()"
                icon="fas fa-plus" />
        </div>

        <x-list-toolbar
            searchPlaceholder="ค้นหาชื่อตำแหน่ง..."
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

        @include('positions.partials.index-table-section')
    </div>

    @include('positions.partials.index-modal')

    <x-delete-warning-modal
        text="ตำแหน่ง"
        formAction="{{ route('positions.destroy', ':id') }}"
        entityUrl="/positions" />

    @include('positions.partials.index-flash-message')
    @include('positions.partials.index-script')
    @include('partials.table-reorder-script')
@endsection
