@extends('layouts.app')
@section('title', 'ตรวจสอบข้อมูลรายวิชาก่อนนำเข้า')
@section('content')
<div class="container-fluid">
    <x-header title="ตรวจสอบข้อมูลก่อนนำเข้า" text="ตรวจรายการใหม่และเลือกรายการซ้ำที่ต้องการอัปเดต" icon="fas fa-file-import" />
    @include('subjects.imports.partials.summary')
    <form method="POST" action="{{ route('subjects.import.confirm', $token) }}" data-subject-import-confirm-form>
        @csrf
        @include('subjects.imports.partials.tables')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <x-button type="primary" buttonType="submit" text="ยืนยันการนำเข้า" icon="fas fa-check"
                :disabled="count($preview['errors']) > 0" />
        </div>
    </form>
    <form method="POST" action="{{ route('subjects.import.cancel', $token) }}" class="mt-2 text-end">
        @csrf
        @method('DELETE')
        <x-button type="secondary" buttonType="submit" text="ยกเลิกและกลับหน้ารายวิชา" icon="fas fa-times" />
    </form>
</div>
@include('subjects.imports.partials.script')
@endsection
