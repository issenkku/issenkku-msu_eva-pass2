<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบประเมินบุคลากร</title>
    <link rel="icon" href="{{ asset('favicon-msu.png') }}?v=1" type="image/png" sizes="32x32">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('favicon-msu.png') }}?v=1">
    @vite(['resources/js/app.ts'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Summernote Rich Text Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-th-TH.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom styling for Summernote */
        .note-editor {
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }
        .note-editor.note-frame {
            border: 1px solid #d1d5db;
        }
        .note-editor.note-frame.note-focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
        }
        .note-toolbar {
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 1050;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            margin: 0;
            color: #495057;
        }

        /* Custom Navbar */
        .navbar-custom {
            background: #060606 !important;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            padding: 15px 0;
        }

        .navbar-brand-custom {
            font-weight: 600;
            font-size: 1.5rem;
            color: #ffffff !important;
            text-decoration: none;
        }

        .navbar-brand-custom:hover {
            color: #eaeef2 !important;
        }

        .nav-link-custom {
            color: #eaeef2 !important;
            font-weight: 500;
            padding: 10px 20px !important;
            border-radius: 4px;
            transition: all 0.2s ease;
            margin: 0 3px;
        }

        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link-custom:hover {
            background: #f8f9fa;
            color: #495057 !important;
        }

        .nav-link-custom.active {
            background: #495057;
            color: white !important;
        }

        /* Custom Toggle Button */
        .navbar-toggler-custom {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 4px;
            background: #ffffff;
        }

        .navbar-toggler-custom:focus {
            box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
        }

        .nav-link:hover {
            background: #28292bff;
            color: #495057;
        }

        .navbar-toggler-icon-custom {
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2873, 80, 87, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Main Container */
        .main-container {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin: 20px auto;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Content Area */
        .content-area {
            padding: 0;
            background: #ffffff;
            min-height: calc(100vh - 200px);
        }

        /* Footer */
        .footer-custom {
            background: #f8f9fa;
            padding: 20px 0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }

        /* Dropdown Menu */
        .dropdown-menu-custom {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
        }

        .dropdown-item-custom {
            border-radius: 0;
            padding: 10px 20px;
            transition: all 0.2s ease;
            color: #495057;
            border: none;
            background: none;
        }

        .dropdown-item-custom:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .dropdown-item-custom.fw-bold {
            background: #495057;
            color: white;
        }

        .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid #dee2e6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Active dropdown indicator */
        .dropdown-toggle.active::after {
            color: #495057;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
        }

        /* Mobile Slide-out Menu */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 2000;
        }

        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #ffffff;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 2001;
            overflow-y: auto;
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        .mobile-menu-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-menu-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #111827;
            font-weight: 700;
            font-size: 1rem;
        }

        .mobile-menu-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #111827;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .mobile-menu-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #495057;
            cursor: pointer;
            padding: 4px;
        }

        .mobile-menu-content {
            padding: 20px 0;
        }

        .mobile-nav-item {
            display: block;
            padding: 15px 20px;
            color: #495057;
            text-decoration: none;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s ease;
            font-weight: 500;
        }

        .mobile-nav-item:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .mobile-dropdown {
            background: #f8f9fa;
        }

        .mobile-dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 15px 20px;
            background: none;
            border: none;
            color: #495057;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 1px solid #dee2e6;
        }

        .mobile-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .mobile-dropdown.active .mobile-dropdown-content {
            max-height: 300px;
        }

        .mobile-dropdown-item {
            display: block;
            padding: 12px 40px;
            color: #6c757d;
            text-decoration: none;
            transition: background-color 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .mobile-dropdown-item:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .mobile-user-section {
            padding: 20px;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #495057;
            font-weight: 500;
        }

        /* Remove unnecessary visual effects */
        .container {
            max-width: 1200px;
        }

        /* Professional styling for buttons */
        .btn {
            border-radius: 4px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #495057;
            border-color: #495057;
        }

        .btn-primary:hover {
            background-color: #343a40;
            border-color: #343a40;
        }

        /* Table styling consistency */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
            font-weight: 600;
        }

        /* Card styling consistency */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
        }

        /* Form styling consistency */
        .form-control:focus {
            border-color: #495057;
            box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
        }

        .form-select:focus {
            border-color: #495057;
            box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
        }

        .text-gray {
            color: #7d7d7d;
        }

        /* Responsive */
        @media (max-width: 1121px) {
            .desktop-nav {
                display: none !important;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        @media (max-width: 480px) {
            .mobile-menu {
                width: 100%;
            }

            .navbar-content {
                padding: 10px 15px;
            }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
</head>

<body class="bg-white font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-black shadow-sm">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center ">
                <h1 class="text-lg font-semibold text-white">
                    ระบบประเมินบุคลากร
                </h1>
                <nav class="d-none d-xl-block">
                    <ul class="nav nav-pills align-items-center gap-2">
                        
                        @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร'))
                            <!-- <li class="nav-item">
                                <a class="nav-link text-white " href="/dashboard">แดชบอร์ด</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center gap-2" href="/manager-dashboard"><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif
                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center gap-2" href="/dashboard"><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif

                        @if(auth()->user() && auth()->user()->hasRole('ผู้ประเมิน'))
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center gap-2" href="/evaluator-dashboard"><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif
                        {{-- @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร') && auth()->user()->position && auth()->user()->position->name == 'คณบดี')
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/evaluatee-dashboard">หน้าตรวจประเมิน</a>
                            </li>
                        @endif --}}
                        @if(auth()->user() && auth()->user()->hasRole('กรรมการ'))
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center gap-2" href="/director-dashboard"><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif
                        @if(auth()->user() && ($showEvaluateeNavigation ?? false))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/evaluatee-dashboard">หน้าประเมินตัวเอง</a>
                            </li>
                        @endif

                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                        <li class="nav-item dropdown">
                            <!-- <a class="nav-link text-white" href="{{ route('users.index') }}">จัดการสมาชิก</a> -->
                             <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="settingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-users"></i>
                                <span>ข้อมูลผู้ใช้</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingDropdown">
                                <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="fas fa-users me-2"></i>จัดการสมาชิก</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.management.log') }}"><i class="fas fa-history me-2"></i>ประวัติการเข้าใช้งาน</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="settingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-list-check"></i>
                                <span>จัดการเกณฑ์</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingDropdown">
                                <li><a class="dropdown-item" href="/criteria-config"><i class="fas fa-sitemap me-2"></i>จัดการโครงสร้างเกณฑ์</a></li>
                                <li><a class="dropdown-item" href="{{ route('assignment-data.index') }}"><i class="fas fa-calendar-check me-2"></i>จัดการรอบการประเมิน</a></li>
                                {{-- <li><a class="dropdown-item" href="{{ route('quality-scores.index') }}">คะแนนคุณภาพ</a></li> --}}
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="settingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i>
                                <span>ตั้งค่า</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingDropdown">
                                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fas fa-globe me-2"></i>ตั้งค่าเว็บไซต์</a></li>
                                <li><a class="dropdown-item" href="{{ route('departments.index') }}"><i class="fas fa-building me-2"></i>ตั้งค่าหน่วยงาน/แผนก</a></li>
                                <li><a class="dropdown-item" href="{{ route('positions.index') }}"><i class="fas fa-briefcase me-2"></i>ตั้งค่าตำแหน่งงาน</a></li>
                                <li><a class="dropdown-item" href="{{ route('job-level.index') }}"><i class="fas fa-layer-group me-2"></i>ตั้งค่าระดับตำแหน่งงาน</a></li>
                                <li><a class="dropdown-item" href="{{ route('subjects.index') }}"><i class="fas fa-book-open me-2"></i>ตั้งค่ารายวิชา</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(auth()->user())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-cog me-2"></i>ตั้งค่าโปรไฟล์</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="fas fa-right-from-bracket me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white d-flex align-items-center" 
                            href="{{ asset('downloads/handbook.pdf') }}" 
                            download="คู่มือการใช้งานระบบประเมินบุคลากร.pdf"
                            title="ดาวน์โหลดคู่มือการใช้งาน">
                                <i class="fas fa-book me-2"></i>
                                <span class="d-none d-xxl-inline">คู่มือ</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>

                <button class="mobile-menu-btn d-block d-xl-none" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>

        <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <span class="mobile-menu-brand">
                    {{-- <span class="mobile-menu-brand-icon">
                        <i class="fas fa-bars"></i>
                    </span> --}}
                    <span>ระบบประเมินบุคลากร</span>
                </span>
                {{-- <button class="mobile-menu-close" onclick="closeMobileMenu()">
                    <i class="fas fa-times"></i>
                </button> --}}
            </div>

            <div class="mobile-menu-content">
                <!-- Role-based Navigation Links -->
                @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร'))
                    <!-- <a href="/dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        แดชบอร์ด
                    </a> -->
                    <a href="/manager-dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าหลัก
                    </a>
                @endif
                
                @if(auth()->user() && auth()->user()->hasRole('ผู้ประเมิน'))
                    <a href="/evaluator-dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าหลัก
                    </a>
                @endif

                @if(auth()->user() && auth()->user()->hasRole('กรรมการ'))
                    <a href="/director-dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าหลัก
                    </a>
                @endif
                
                @if(auth()->user() && ($showEvaluateeNavigation ?? false))
                    <a href="/evaluatee-dashboard" class="mobile-nav-item">
                        <i class="fas fa-user-check" style="width: 20px; margin-right: 10px;"></i>
                        หน้าประเมินตัวเอง
                    </a>
                @endif
                
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <a href="/dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าแรก
                    </a>
                    <a href="{{ route('users.index') }}" class="mobile-nav-item">
                        <i class="fas fa-users" style="width: 20px; margin-right: 10px;"></i>
                        จัดการสมาชิก
                    </a>
                    <a href="{{ route('user.management.log') }}" class="mobile-nav-item">
                        <i class="fas fa-history" style="width: 20px; margin-right: 10px;"></i>
                        ประวัติการเข้าใช้งาน
                    </a>
                    <a href="/criteria-config" class="mobile-nav-item">
                        <i class="fas fa-cogs" style="width: 20px; margin-right: 10px;"></i>
                        จัดการโครงสร้างเกณฑ์
                    </a>
                    <a href="{{ route('assignment-data.index') }}" class="mobile-nav-item">
                        <i class="fas fa-tasks" style="width: 20px; margin-right: 10px;"></i>
                        จัดการรอบการประเมิน
                    </a>
                    {{-- <a href="{{ route('quality-scores.index') }}" class="mobile-nav-item">
                        <i class="fas fa-star" style="width: 20px; margin-right: 10px;"></i>
                        คะแนนคุณภาพ
                    </a> --}}
                    
                    <!-- Settings Dropdown for Mobile -->
                    <div class="mobile-dropdown" id="settingsDropdown">
                        <button class="mobile-dropdown-toggle" onclick="toggleMobileDropdown('settingsDropdown')">
                            <span>
                                <i class="fas fa-cog" style="width: 20px; margin-right: 10px;"></i>
                                ตั้งค่า
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-300"></i>
                        </button>
                        <div class="mobile-dropdown-content">
                            <a href="{{ route('settings.index') }}" class="mobile-dropdown-item">ตั้งค่าเว็บไซต์</a>
                            <a href="{{ route('departments.index') }}" class="mobile-dropdown-item">ตั้งค่าหน่วยงาน/แผนก</a>
                            <a href="{{ route('positions.index') }}" class="mobile-dropdown-item">ตั้งค่าตำแหน่งงาน</a>
                            <a href="{{ route('job-level.index') }}" class="mobile-dropdown-item">ตั้งค่าระดับตำแหน่งงาน</a>
                            <a href="{{ route('subjects.index') }}" class="mobile-dropdown-item">ตั้งค่ารายวิชา</a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- User Section -->
            @if(auth()->user())
            <div class="mobile-user-section">
                <div class="mobile-user-info">
                    <i class="fa fa-user"></i>
                    <span>{{ auth()->user()->name }}</span>
                </div>
                <a href="/profile" class="mobile-nav-item" style="padding: 10px 0; border: none;">
                    <i class="fas fa-user-edit" style="width: 20px; margin-right: 10px;"></i>
                    ตั้งค่าโปรไฟล์
                </a>
                <a href="{{ asset('downloads/handbook.pdf') }}"  class="mobile-nav-item" 
                    style="padding: 10px 0; border: none;" 
                    title="ดาวน์โหลดคู่มือการใช้งาน"
                    download="คู่มือการใช้งานระบบประเมินบุคลากร.pdf">
                    <i class="fas fa-book me-2" style="width: 20px; margin-right: 10px;"></i>
                    คู่มือ
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="mobile-nav-item" style="padding: 10px 0; border: none; color: #dc3545; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt" style="width: 20px; margin-right: 10px;"></i>
                        Logout
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <!-- JavaScript Functions - Always Available -->
    <script>
        function toggleDropdown(button) {
            const dropdown = button.parentElement;
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown.active').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active', !isActive);
        }

        // Toggle mobile menu
        function toggleMobileMenu() {
            const overlay = document.getElementById('mobileMenuOverlay');
            const menu = document.getElementById('mobileMenu');
            
            overlay.classList.add('active');
            menu.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close mobile menu
        function closeMobileMenu() {
            const overlay = document.getElementById('mobileMenuOverlay');
            const menu = document.getElementById('mobileMenu');
            
            overlay.classList.remove('active');
            menu.classList.remove('active');
            document.body.style.overflow = '';
            
            // Close all mobile dropdowns
            document.querySelectorAll('.mobile-dropdown.active').forEach(d => {
                d.classList.remove('active');
            });
        }

        // Toggle mobile dropdown
        function toggleMobileDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const icon = dropdown.querySelector('.fa-chevron-down');
            
            dropdown.classList.toggle('active');
            
            // Rotate icon
            if (dropdown.classList.contains('active')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Simplified navbar behavior - remove auto-hide for professional look
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (navbar && window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            } else if (navbar) {
                navbar.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.08)';
            }
        });

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '1';
        });

        // Active dropdown highlight
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item-custom');
            dropdownItems.forEach(item => {
                if (item.classList.contains('fw-bold')) {
                    const dropdown = item.closest('.dropdown');
                    if (dropdown) {
                        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                        if (dropdownToggle) {
                            dropdownToggle.classList.add('active');
                        }
                    }
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event.target)) {
                    const bsDropdown = new bootstrap.Dropdown(dropdown.previousElementSibling);
                    bsDropdown.hide();
                }
            });
        });
    </script>

    @if (session('success'))
        <script>
            // Show success message if needed
            // alert('{{ session('success') }}');
        </script>
    @endif
</body>

</html>
