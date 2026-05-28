@extends('layouts.app')
@section('content')
    {{-- หน้ากำหนดช่วงเวลาและผู้เกี่ยวข้องสำหรับการ assign เกณฑ์ --}}
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- ฟอร์มหลักของหน้า assign ใช้ id เดียวกับ script partial เพื่อผูก event ให้ตรงกัน --}}
            <form id="assignment-form" action="#" method="POST">
                @csrf

                @include('criteria_config.partials.assign-period-section')
                @include('criteria_config.partials.assign-participants-section')
                @include('criteria_config.partials.assign-form-actions')
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- script ของหน้า assign ถูกแยกออกทั้งก้อนเพื่อลดความรกในไฟล์หลัก --}}
    @include('criteria_config.partials.assign-script')
@endpush
