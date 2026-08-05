{{-- รวมโมดัลหลักของหน้าจัดการผู้ใช้งานไว้ในจุดเดียว --}}
@include('user.management.user-form-modal')
@include('user.management.import-user-modal')
@include('user.management.partials.bulk-delete-modal')
@include('user.management.partials.bulk-status-modal')
<x-delete-warning-modal
    text="เจ้าหน้าที่"
    formAction="{{ route('users.destroy', ':id') }}"
    entityUrl="/users"
    :async="true"
/>
