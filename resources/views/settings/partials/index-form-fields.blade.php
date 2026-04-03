{{-- ฟิลด์หลักของหน้าตั้งค่า ใช้เป็นแหล่งข้อมูลหลักที่บันทึกลงระบบ --}}
<div class="form-group-custom">
    <label for="university" class="form-label-custom">
        ชื่อมหาวิทยาลัย <span style="color: #dc3545;">*</span>
    </label>
    <div class="input-group-custom">
        <input type="text" name="university" id="university"
            class="form-control form-control-custom"
            placeholder="กรุณาระบุชื่อมหาวิทยาลัย"
            value="{{ old('university', $setting->university ?? '') }}"
            required>
        <i class="form-icon fas fa-university"></i>
    </div>
    @error('university')
        <div class="alert alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-group-custom">
    <label for="faculty" class="form-label-custom">
        ชื่อคณะ <span style="color: #dc3545;">*</span>
    </label>
    <div class="input-group-custom">
        <input type="text" name="faculty" id="faculty"
            class="form-control form-control-custom"
            placeholder="กรุณาระบุชื่อคณะ"
            value="{{ old('faculty', $setting->faculty ?? '') }}"
            required>
        <i class="form-icon fas fa-graduation-cap"></i>
    </div>
    @error('faculty')
        <div class="alert alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $message }}
        </div>
    @enderror
</div>

<div class="form-group-custom">
    <label for="notification_days" class="form-label-custom">
        จำนวนวันแจ้งเตือนทางอีเมล <span style="color: #dc3545;">*</span>
    </label>
    <div class="input-group-custom">
        <input type="number" name="notification_days" id="notification_days"
            class="form-control form-control-custom"
            placeholder="กรุณาระบุจำนวนวันล่วงหน้าที่ต้องการให้แจ้งเตือน (1-30 วัน)"
            min="1" max="30"
            value="{{ old('notification_days', $setting->notification_days ?? 7) }}"
            required>
        <i class="form-icon fas fa-bell"></i>
    </div>
    @error('notification_days')
        <div class="alert alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $message }}
        </div>
    @enderror
    <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i>
        ระบบจะส่งอีเมลแจ้งเตือนก่อนถึงวันสิ้นสุดการประเมินตามจำนวนวันที่ระบุ (1-30 วัน)
    </small>
</div>
