{{-- แถบกรองและค้นหา ใช้คุมมุมมองข้อมูลเจ้าหน้าที่ก่อน render ตาราง --}}
<div class="flex flex-wrap gap-4 mb-4 justify-between">
    @include('user.management.partials.index-filter-form', ['personnelTypes' => $personnelTypes])
    @include('user.management.partials.index-search-section')
</div>
