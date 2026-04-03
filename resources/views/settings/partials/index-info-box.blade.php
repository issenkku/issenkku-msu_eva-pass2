{{-- กล่องสรุปข้อมูลปัจจุบัน ใช้ช่วยให้ผู้ดูแลเห็นค่าที่กำลังถูกใช้งานอยู่ในระบบ --}}
<div class="form-group-custom">
    <div class="info-box">
        <h6>
            <i class="fas fa-info-circle me-2"></i>ข้อมูลปัจจุบัน
        </h6>
        <p>
            <strong>มหาวิทยาลัย:</strong> {{ $setting->university }}
        </p>
        <p>
            <strong>คณะ:</strong> {{ $setting->faculty }}
        </p>
        <p>
            <strong>จำนวนวันแจ้งเตือน:</strong> {{ $setting->notification_days ?? 7 }} วัน
        </p>
        <small>
            อัปเดตล่าสุด: {{ $setting->updated_at->format('d/m/Y H:i') }} น.
        </small>
    </div>
</div>
