@extends('layout')
@section('content')
    <style>
        .form-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .card-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 25px;
            text-align: center;
            border: none;
        }

        .card-header-custom h1 {
            margin: 0;
            font-weight: 600;
            font-size: 2rem;
        }

        .form-group-custom {
            margin-bottom: 25px;
            position: relative;
        }

        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control-custom:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            outline: none;
        }

        .form-label-custom {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .btn-custom {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            margin: 5px;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #56ab2f, #a8e6cf);
            color: white;
        }

        .btn-success-custom:hover {
            background: linear-gradient(135deg, #4a9929, #96d9bb);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(86, 171, 47, 0.4);
        }

        .btn-secondary-custom {
            background: linear-gradient(135deg, #bdc3c7, #95a5a6);
            color: white;
        }

        .btn-secondary-custom:hover {
            background: linear-gradient(135deg, #a9b2b8, #7f8c8d);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(149, 165, 166, 0.4);
        }

        .alert-custom {
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            margin-top: 10px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            animation: shake 0.5s ease-in-out;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #56ab2f, #a8e6cf);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 18px;
        }

        .input-group-custom {
            position: relative;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-new {
            background: linear-gradient(135deg, #56ab2f, #a8e6cf);
            color: white;
        }

        .status-update {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
            color: white;
        }
    </style>

    <div class="form-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h1>✨ ข้อมูลหน้าที่ ✨</h1>
                            <p class="mb-0" style="opacity: 0.9;">
                                กรอกข้อมูลมหาวิทยาลัยและคณะ
                                @if(isset($settings) && $setting)
                                    <span class="status-badge status-update">อัปเดตข้อมูล</span>
                                @else
                                    <span class="status-badge status-new">สร้างข้อมูลใหม่</span>
                                @endif
                            </p>
                        </div>
                        <div class="card-body p-5">
                            {{-- แสดงข้อความสำเร็จ --}}
                            @if(session('success'))
                                <div class="alert alert-success-custom">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            <form action="{{ route('settings.store') }}" method="POST">
                                @csrf
                                @if(isset($settings) && $setting)
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $setting->id }}">
                                @endif

                                <!-- ชื่อมหาวิทยาลัย -->
                                <div class="form-group-custom">
                                    <label for="university" class="form-label-custom">
                                        🏛️ ชื่อมหาวิทยาลัย
                                    </label>
                                    <div class="input-group-custom">
                                        <input type="text" name="university" id="university"
                                            class="form-control form-control-custom" 
                                            placeholder="กรุณาใส่ชื่อมหาวิทยาลัย"
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
                                        🎓 ชื่อคณะ
                                    </label>
                                    <div class="input-group-custom">
                                        <input type="text" name="faculty" id="faculty"
                                            class="form-control form-control-custom" 
                                            placeholder="กรุณาใส่ชื่อคณะ"
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

                                <!-- แสดงข้อมูลปัจจุบัน -->
                                @if(isset($settings) && $setting)
                                    <div class="form-group-custom">
                                        <div style="background: #e3f2fd; border-radius: 12px; padding: 15px; border-left: 4px solid #2196f3;">
                                            <h6 style="color: #1976d2; margin-bottom: 10px;">
                                                <i class="fas fa-info-circle me-2"></i>ข้อมูลปัจจุบัน
                                            </h6>
                                            <p style="margin: 5px 0; color: #424242;">
                                                <strong>มหาวิทยาลัย:</strong> {{ $setting->university }}
                                            </p>
                                            <p style="margin: 5px 0; color: #424242;">
                                                <strong>คณะ:</strong> {{ $setting->faculty }}
                                            </p>
                                            <small style="color: #666;">
                                                อัปเดตล่าสุด: {{ $setting->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <!-- ปุ่มส่งและกลับ -->
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-success-custom">
                                        <i class="fas fa-save me-2"></i>
                                        @if(isset($settings) && $setting)
                                            อัปเดตข้อมูล
                                        @else
                                            บันทึกข้อมูล
                                        @endif
                                    </button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-custom btn-secondary-custom">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        กลับ
                                    </a>
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