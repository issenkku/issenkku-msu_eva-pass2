{{-- badge สิทธิ์ของบทบาทแต่ละรายการ --}}
@foreach ($permissions as $permission)
    <span class="permission-badge">{{ $permission->name }}</span>
@endforeach
