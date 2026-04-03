{{-- flash message ของหน้าคะแนนคุณภาพ ใช้กับทั้ง success และ error --}}
@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    </div>
@endif
