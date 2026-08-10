{{-- ฟิลด์หลักของหน้าตั้งค่า ใช้เป็นแหล่งข้อมูลหลักที่บันทึกลงระบบ --}}
<div class="form-group-custom">
    @php($selectedBackgroundPath = old('selected_background_path', $setting?->background_path))
    <label for="background" class="form-label-custom">
        รูปพื้นหลังหน้าภาระงาน
    </label>
    <div class="background-upload-row">
        <div class="background-preview">
            <img id="backgroundPreview"
                src="{{ $setting?->background_url ?? asset('images/workload-background.jpg') }}"
                data-default-background="{{ asset('images/workload-background.jpg') }}"
                alt="รูปพื้นหลังหน้าภาระงาน">
        </div>
        <div class="logo-upload-control">
            <input type="hidden" name="selected_background_path" id="selectedBackgroundPath" value="{{ $selectedBackgroundPath }}">
            <div id="deletedBackgroundInputs"></div>
            <input type="file" name="background" id="background"
                class="form-control form-control-custom"
                accept="image/png,image/jpeg,image/webp">
            <small class="field-help">
                รองรับ PNG, JPG หรือ WEBP ขนาดไม่เกิน 4MB ใช้เป็นพื้นหลังของหน้ากรอกภาระงาน
            </small>
            @include('settings.partials.background-library')
            @if($setting?->background_path)
                <label class="remove-logo-option">
                    <input type="checkbox" name="remove_background" value="1">
                    กลับไปใช้พื้นหลังเริ่มต้น
                </label>
            @endif
            <label class="remove-logo-option">
                <input type="checkbox" name="use_white_background" id="useWhiteBackground" value="1"
                    @checked(old('use_white_background', $setting?->use_white_background ?? false))>
                ใช้พื้นหลังสีขาว
            </label>
        </div>
    </div>
    @error('background')
        <div class="alert alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $message }}
        </div>
    @enderror
    @error('selected_background_path')
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
    <small class="field-help">
        <i class="fas fa-info-circle me-1"></i>
        ระบบจะส่งอีเมลแจ้งเตือนก่อนถึงวันสิ้นสุดการประเมินตามจำนวนวันที่ระบุ (1-30 วัน)
    </small>
</div>
