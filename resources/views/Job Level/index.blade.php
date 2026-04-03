@extends('layouts.app')
@section('title', 'จัดการข้อมูลระดับตำแหน่งงาน')

@section('content')
    @include('Job Level.partials.index-styles')

    <div class="container-fluid">
        <x-header
            title="จัดการข้อมูลระดับตำแหน่งงาน"
            text="ระบบจัดการข้อมูลระดับตำแหน่งงาน"
            icon="fas fa-user-tie" />

        <div class="d-flex justify-content-end mb-3">
            <x-button
                type="primary"
                buttonType="button"
                text="เพิ่มระดับตำแหน่งงาน"
                onclick="openCreateModal()"
                icon="fas fa-plus" />
        </div>

        <x-list-toolbar
            searchPlaceholder="ค้นหาระดับตำแหน่งงาน..."
            :sortOptions="[
                'manual' => 'จัดอันดับเอง',
                'latest' => 'ใหม่สุด',
                'oldest' => 'เก่าสุด',
                'name_asc' => 'ชื่อ A-Z',
                'name_desc' => 'ชื่อ Z-A',
            ]"
        />

        @include('Job Level.partials.index-table-section')
    </div>

    @include('Job Level.partials.index-modal')

    <x-delete-warning-modal
        text="ระดับตำแหน่งงาน"
        formAction="{{ route('job-level.destroy', ':id') }}"
        entityUrl="/job-level" />

    @include('Job Level.partials.index-flash-message')
    @include('Job Level.partials.index-script')
    @include('partials.table-reorder-script')
@endsection
