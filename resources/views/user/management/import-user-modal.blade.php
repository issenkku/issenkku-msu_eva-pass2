{{-- โมดัลนำเข้าผู้ใช้งานแบบไฟล์ พร้อมส่วนแสดงข้อผิดพลาดและสคริปต์ควบคุม --}}
<div id="importUserModal" class="fixed z-[9999] inset-0 bg-black bg-opacity-50 hidden items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-4xl mx-4 p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl">
        {{-- ส่วนหัวของโมดัล --}}
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-purple-700">นำเข้าข้อมูลผู้ใช้งาน</h2>
                <p class="text-sm text-gray-600 mt-1">อัปโหลดไฟล์ Excel หรือ CSV เพื่อนำเข้าข้อมูลผู้ใช้งานจำนวนมาก</p>
            </div>
            <button type="button" data-import-modal-close class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
        </div>

        <form id="importForm" action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            @include('user.management.partials.import-modal-upload-section')
            @include('user.management.partials.import-modal-feedback')

            {{-- ส่วนปุ่มคำสั่ง --}}
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
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
