<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบประเมินบุคลากร</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        * {
            font-family: 'Kanit', sans-serif;
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

        /* Active dropdown indicator */
        .dropdown-toggle.active::after {
            color: #495057;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                border-radius: 4px;
            }

            .content-area {
                padding: 0;
            }

            .navbar-brand-custom {
                font-size: 1.2rem;
            }

            .nav-link-custom {
                padding: 8px 15px !important;
                margin: 2px 0;
            }

            .dropdown-menu-custom {
                margin-top: 5px;
            }
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
    </style>

    <!-- ภาษาไทย -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>


</head>

<body class="bg-white font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-black shadow-sm">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-lg font-semibold text-white">
                    ระบบประเมินบุคลากร
                </h1>
                <nav>
                    <ul class="nav nav-pills align-items-center gap-2">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/">หน้าแรก</a>
                        </li>
                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('users.index') }}">จัดการสมาชิก</a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/criteria-config">จัดการโครงสร้างเกณฑ์</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('assignment-data.index') }}">จัดการรอบการประเมิน</a>
                        </li>
                        @if(auth()->user() && auth()->user()->hasRole('admin'))
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
        <script>
            alert('{{ session('success') }}');
        </script>

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
