{{-- กล่องสรุปการนำค่าตั้งค่าไปใช้จริงในระบบ --}}
<div class="form-group-custom">
    <div class="info-box settings-impact-box">
        <h6>
            <i class="fas fa-circle-info me-2"></i>ค่านี้ถูกใช้ที่ไหน
        </h6>

        <div class="settings-preview">
            <span class="settings-preview-label">ตัวอย่างข้อมูลกลาง</span>
            <span class="settings-preview-identity">
                <img src="{{ $setting->logo_url }}" alt="โลโก้หน่วยงาน">
                <strong>{{ $setting->faculty }} {{ $setting->university }}</strong>
            </span>
            <span>ระบบจะแจ้งเตือนล่วงหน้า {{ $setting->notification_days ?? 7 }} วันก่อนสิ้นสุดรอบประเมิน</span>
        </div>

        <div class="settings-impact-grid">
            <div class="settings-impact-card">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>อีเมลแจ้งเตือน</strong>
                    <p>ใช้จำนวนวันแจ้งเตือนเพื่อส่งอีเมลก่อนรอบประเมินสิ้นสุด</p>
                </div>
            </div>

            <div class="settings-impact-card">
                <i class="fas fa-building-columns"></i>
                <div>
                    <strong>ตัวตนหน่วยงานกลาง</strong>
                    <p>ใช้แสดงโลโก้ ชื่อคณะ และชื่อมหาวิทยาลัยในหน้าเข้าสู่ระบบ</p>
                </div>
            </div>

            <div class="settings-impact-card">
                <i class="fas fa-file-lines"></i>
                <div>
                    <strong>พร้อมต่อยอดเอกสาร</strong>
                    <p>ยังสามารถนำไปใช้ต่อกับหัวรายงาน อีเมล หรือเอกสารประเมินได้ในขั้นถัดไป</p>
                </div>
            </div>
        </div>

        <small>
            อัปเดตล่าสุด: {{ optional($setting->updated_at)->format('d/m/Y H:i') ?? '-' }} น.
        </small>
    </div>
</div>
