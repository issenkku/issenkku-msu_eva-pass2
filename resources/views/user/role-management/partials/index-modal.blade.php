{{-- modal สำหรับเพิ่มบทบาทใหม่และกำหนดสิทธิ์ในครั้งเดียว --}}
<div id="roleModal" class="role-modal-backdrop">
    <div class="role-modal-card">
        <h2 id="modalTitle" class="role-modal-title">เพิ่มบทบาท</h2>

        <form method="POST" id="roleForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="text" name="name" id="roleName" placeholder="ชื่อบทบาท" class="role-input">

            <label class="block mb-2 font-medium">สิทธิ์</label>
            <div class="role-permission-grid">
                @foreach($permissions as $permission)
                    <label>
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="permission-checkbox">
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>

            <div class="role-modal-actions">
                <button type="button" onclick="closeModal()" class="role-cancel-button">ยกเลิก</button>
                <button type="submit" class="role-save-button">บันทึก</button>
            </div>
        </form>
    </div>
</div>
