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

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
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
    </style>

    <div class="form-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h1>✨ ข้อมูลหน้าที่ ✨</h1>
                            <p class="mb-0" style="opacity: 0.9;">กรอกข้อมูลมหาวิทยาลัยและคณะ</p>
                        </div>
                        <div class="card-body p-5">
                            <form action="{{ route('settings.store') }}" method="POST">
                                @csrf

                                <!-- ชื่อมหาวิทยาลัย -->
                                <div class="form-group-custom">
                                    <label for="university" class="form-label-custom">
                                        🏛️ ชื่อมหาวิทยาลัย
                                    </label>
                                    <div class="input-group-custom">
                                        <input type="text" name="university" id="university"
                                            class="form-control form-control-custom" placeholder="กรุณาใส่ชื่อมหาวิทยาลัย"
                                            value="{{ session('last.university') ?? old('university') }}">
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
                                            class="form-control form-control-custom" placeholder="กรุณาใส่ชื่อคณะ"
                                            value="{{ session('last.faculty') ?? old('faculty') }}">
                                        <i class="form-icon fas fa-graduation-cap"></i>
                                    </div>
                                    @error('faculty')
                                        <div class="alert alert-custom">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- ปุ่มส่งและกลับ -->
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-success-custom">
                                        <i class="fas fa-save me-2"></i>
                                        บันทึกข้อมูล
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
