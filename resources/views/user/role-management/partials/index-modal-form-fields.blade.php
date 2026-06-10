{{-- ฟิลด์หลักของโมดัลสร้างบทบาท --}}
<input type="hidden" name="_method" id="formMethod" value="POST">
<input type="text" name="name" id="roleName" placeholder="ชื่อบทบาท" class="role-input">

<div class="block mb-2 font-medium">สิทธิ์</div>
<div class="role-permission-grid">
    @foreach($permissions as $permission)
        <label>
            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="permission-checkbox">
            {{ $permission->name }}
        </label>
    @endforeach
</div>
