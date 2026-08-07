{{-- โมดัลนำเข้าผู้ใช้งานแบบไฟล์ พร้อมส่วนแสดงข้อผิดพลาดและสคริปต์ควบคุม --}}
<div id="importUserModal" class="fixed z-[9999] inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-0 sm:p-4">
    <div
        data-import-modal-panel
        class="flex h-[100dvh] max-h-[100dvh] w-full max-w-4xl flex-col overflow-hidden rounded-none bg-white shadow-2xl sm:h-auto sm:max-h-[calc(100dvh-2rem)] sm:rounded-xl">
        {{-- ส่วนหัวของโมดัล --}}
        <div data-import-modal-header class="flex flex-none items-center justify-between border-b px-4 py-4 sm:px-6">
            <div>
                <h2 class="text-xl font-semibold text-purple-700">นำเข้าข้อมูลผู้ใช้งาน</h2>
                <p class="text-sm text-gray-600 mt-1">อัปโหลดไฟล์ Excel หรือ CSV เพื่อนำเข้าข้อมูลผู้ใช้งานจำนวนมาก</p>
            </div>
            <button type="button" data-import-modal-close class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
        </div>

        <form id="importForm" class="flex min-h-0 flex-1 flex-col" action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
            <div data-import-modal-body class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                @include('user.management.partials.import-modal-upload-section')
                @include('user.management.partials.import-modal-feedback')
            </div>

            {{-- ส่วนปุ่มคำสั่ง --}}
            <div data-import-modal-footer class="flex flex-none justify-end gap-4 border-t px-4 py-4 sm:px-6">
                <x-button
                    type="secondary"
                    text="ย้อนกลับ"
                    data-import-modal-close
                    icon="fas fa-arrow-left" />

                <button
                    type="submit"
                    id="submitBtn"
                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    <span id="submitText">นำเข้าข้อมูล</span>
                    <svg id="loadingIcon" class="hidden animate-spin -mr-1 ml-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@include('user.management.partials.import-modal-flash-message')
@include('user.management.partials.import-modal-styles')
@include('user.management.partials.import-modal-script')
