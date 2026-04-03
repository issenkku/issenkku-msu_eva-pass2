{{-- ส่วนหัวของหน้าคะแนนคุณภาพ พร้อมปุ่มไปยังหน้าสร้างรายการใหม่ --}}
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-star me-2"></i>จัดการคะแนนคุณภาพ</h4>
        <p class="mb-0 text-muted">ระบบจัดการคะแนนคุณภาพสำหรับผู้ใช้งาน</p>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('quality-scores.create') }}" class="btn-primary">
        <i class="fas fa-plus me-2"></i>เพิ่มคะแนนคุณภาพ
    </a>
</div>
