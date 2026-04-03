@extends('layouts.app')
@section('title', 'จัดการข้อมูลรายวิชา')

@section('content')
    @include('subjects.partials.index-styles')

    <div class="container-fluid">
        <x-header
            title="จัดการข้อมูลรายวิชา"
            text="ระบบจัดการข้อมูลรายวิชา"
            icon="fas fa-book" />

        <div class="d-flex justify-content-end mb-3">
            <x-button
                type="primary"
                buttonType="button"
                text="เพิ่มรายวิชา"
                onclick="openCreateModal()"
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
            ]"
        />

        @include('subjects.partials.index-table-section')
    </div>

    <x-subject-modal />

    <x-delete-warning-modal
        text="รายวิชา"
        formAction="{{ route('subjects.destroy', ':id') }}"
        entityUrl="/subjects" />

    @include('subjects.partials.index-flash-message')
    @include('subjects.partials.index-script')
    @include('partials.table-reorder-script')
@endsection
