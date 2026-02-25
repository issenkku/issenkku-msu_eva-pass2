@extends('layouts.app')
@section('content')
    <style>
        .form-container {
            background: #ffffff;
            min-height: 100vh;
            padding: 40px 0;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header-custom {
            background: #f3e8ff;
            color: #495057;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .card-header-custom h1 {
            margin: 0;
            font-weight: 600;
            font-size: 1.75rem;
            color: #212529;
        }

        .card-header-custom p {
            margin: 10px 0 0 0;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .form-group-custom {
            margin-bottom: 24px;
            position: relative;
        }

        .form-control-custom {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #ffffff;
            color: #495057;
        }

        .form-control-custom:focus {
            border-color: #495057;
            box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
            background: #ffffff;
            outline: none;
        }

        .form-label-custom {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .btn-custom {
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            border: 1px solid;
            margin: 5px;
        }

        .btn-primary-custom {
            background: #495057;
            border-color: #495057;
            color: white;
        }

        .btn-primary-custom:hover {
            background: #343a40;
            border-color: #343a40;
            color: white;
        }

        .btn-secondary-custom {
            background: #ffffff;
            border-color: #ced4da;
            color: #6c757d;
        }

        .btn-secondary-custom:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            color: #495057;
        }

        .alert-custom {
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px 16px;
            margin-top: 8px;
            background: #f8d7da;
            color: #721c24;
        }

        .alert-success-custom {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .form-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 16px;
        }

        .input-group-custom {
            position: relative;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            border-left: 4px solid #495057;
        }

        .info-box h6 {
            color: #495057;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        .info-box p {
            margin: 8px 0;
            color: #495057;
            font-size: 14px;
        }

        .info-box small {
            color: #6c757d;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .me-2 {
            margin-right: 0.5rem;
        }

        .p-5 {
            padding: 40px;
        }

        /* Remove emoji styling for more formal look */
        .form-label-custom::before {
            content: '';
            margin-right: 0;
        }
    </style>

    {{--  --}}
    <div class="form-container">
        {{-- บล็อกเนื้อหา --}}
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h1>ข้อมูลมหาวิทยาลัย</h1>
                            <p class="mb-0">กรอกข้อมูลมหาวิทยาลัยและคณะ</p>
                        </div>
                        <div class="card-body p-5">
                            {{-- แสดงข้อความสำเร็จ --}}
                            @if(session('success'))
                                <div class="alert alert-success-custom">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- ฟอร์ม --}}
                            <form action="{{ route('settings.store') }}" method="POST">
                                @csrf
                                @if(isset($settings) && $setting)
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $setting->id }}">
                                @endif

                                <!-- ชื่อมหาวิทยาลัย -->
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

                                <!-- ชื่อคณะ -->
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

                                <!-- จำนวนวันแจ้งเตือน -->
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

                                <!-- แสดงข้อมูลปัจจุบัน -->
                                @if(isset($settings) && $setting)
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
                                @endif

                                <!-- ปุ่มส่งและกลับ -->
                                <div class="text-center mt-4">
                                    @if(isset($settings) && $setting)
                                        <x-button 
                                            type="primary" 
                                            text="อัปเดตข้อมูล" 
                                            icon="fas fa-save" 
                                            buttonType="submit" 
                                        />
                                    @else
                                        <x-button 
                                            type="primary" 
                                            text="บันทึกข้อมูล" 
                                            icon="fas fa-save" 
                                            buttonType="submit" 
                                        />
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- เพิ่ม Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection
