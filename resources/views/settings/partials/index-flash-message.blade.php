{{-- flash message หลังบันทึกค่าตั้งค่าสำเร็จ --}}
@if(session('success'))
    <div class="alert alert-success-custom">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
    </div>
@endif
