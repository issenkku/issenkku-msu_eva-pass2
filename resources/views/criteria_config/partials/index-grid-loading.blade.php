{{-- grid สำหรับรายการเกณฑ์ โดยเริ่มจาก loading state ก่อน fetch ข้อมูลจริง --}}
<div id="criteria-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div id="criteria-loading" class="col-span-3 flex justify-center py-10">
        <span class="text-gray-500">Loading...</span>
    </div>
</div>
