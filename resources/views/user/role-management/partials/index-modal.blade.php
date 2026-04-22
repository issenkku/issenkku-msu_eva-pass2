{{-- modal สำหรับเพิ่มบทบาทใหม่และกำหนดสิทธิ์ในครั้งเดียว --}}
<div id="roleModal" class="role-modal-backdrop">
    <div class="role-modal-card">
        @include('user.role-management.partials.index-modal-title')

        <form method="POST" id="roleForm">
            @csrf
            @include('user.role-management.partials.index-modal-form-fields')
            @include('user.role-management.partials.index-modal-actions')
        </form>
    </div>
</div>
