{{-- ไฟล์มุมมอง: resources/views/user/management/user-form-modal.blade.php --}}
<div id="userModal" class="fixed inset-0 z-[9999] hidden items-baseline justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative top-10 w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6">
        @include('user.management.partials.user-modal-header')

        <form id="userForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            @include('user.management.partials.user-modal-error-list')

            <div class="mt-4 grid grid-cols-1 gap-6">
                @include('user.management.partials.user-modal-personal-section')
                @include('user.management.partials.user-modal-work-section')
                @include('user.management.partials.user-modal-contact-section')
                @include('user.management.partials.user-modal-education-section')
                @include('user.management.partials.user-modal-password-section')
                @include('user.management.partials.user-modal-settings-section')
            </div>

            @include('user.management.partials.user-modal-actions')
        </form>
    </div>
</div>

@include('user.management.partials.user-modal-script')
