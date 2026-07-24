<div
    id="userModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black bg-opacity-50 p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="userModalTitle">
    <div
        data-user-modal-panel
        class="flex min-w-0 max-w-full max-h-[calc(100dvh-2rem)] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl sm:max-h-[calc(100dvh-3rem)]">
        @include('user.management.partials.user-modal-header')

        <form
            id="userForm"
            class="flex min-h-0 min-w-0 flex-1 flex-col"
            action="{{ route('users.store') }}"
            method="POST">
            <div data-user-modal-scroll class="min-w-0 min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
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
            </div>

            @include('user.management.partials.user-modal-actions')
        </form>
    </div>
</div>

@include('user.management.partials.user-modal-script')
