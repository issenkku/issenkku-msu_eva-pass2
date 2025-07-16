<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบประเมินบุคลากร</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/2.0.0/trix.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- ภาษาไทย -->
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
                <nav>
                    <ul class="nav nav-pills align-items-center gap-2">
                        @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/dashboard">หน้าแรก</a>
                            </li>
                        @endif
                        @if(auth()->user() && auth()->user()->hasRole('ผู้ประเมิน'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/evaluator-dashboard">หน้าการประเมิน</a>
                            </li>
                        @endif
                        @if(auth()->user() && auth()->user()->hasRole('ผู้รับการประเมิน'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/evaluatee-dashboard">หน้าการประเมิน</a>
                            </li>
                        @endif
                        
                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/dashboard">หน้าแรก</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('users.index') }}">จัดการสมาชิก</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/criteria-config">จัดการโครงสร้างเกณฑ์</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('assignment-data.index') }}">จัดการรอบการประเมิน</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="settingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ตั้งค่า
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingDropdown">
                                <li><a class="dropdown-item" href="{{ route('settings.index') }}">ตั้งค่าเว็บไซต์</a></li>
                                <li><a class="dropdown-item" href="{{ route('departments.index') }}">ตั้งค่าหน่วยงาน/แผนก</a></li>
                                <li><a class="dropdown-item" href="{{ route('positions.index') }}">ตั้งค่าตำแหน่งงาน</a></li>
                            </ul>
                        </li>
                        @endif
                        @if(auth()->user())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/profile">ตั้งค่าโปรไฟล์</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @if (session('success'))
        {{-- <script>
            alert('{{ session('success') }}');
        </script> --}}

        <script>
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
                if (window.scrollY > 50) {
                    navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
                } else {
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
                        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                        dropdownToggle.classList.add('active');
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event
                            .target)) {
                        const bsDropdown = new bootstrap.Dropdown(dropdown.previousElementSibling);
                        bsDropdown.hide();
                    }
                });
            });
        </script>
    @endif


</body>

</html>
