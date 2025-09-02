<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบประเมินบุคลากร')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            position: fixed;
            top: 0;
        }

        .navbar-custom.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
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

            .nav-link-custom i,
            .dropdown-item-custom i {
                width: 1.25rem;
                text-align: center;
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
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a href="#" class="navbar-brand navbar-brand-custom">
                <i class="fas fa-chart-line me-2 text-gray"></i>
                ระบบประเมินบุคลากร
            </a>

            <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon navbar-toggler-icon-custom"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- หน้าหลัก/Dashboard -->
                    {{-- <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->routeIs('dashboard') || request()->is('/') ? 'active' : '' }}" 
                           href="#">
                            <i class="fas fa-home me-2"></i>
                            หน้าแรก
                        </a>
                    </li> --}}

                    <!-- จัดการข้อมูล Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle 
                           {{ request()->routeIs(['settings.*', 'departments.*', 'positions.*']) ? 'active' : '' }}"
                            href="#" id="navbarDataDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-database me-2 text-gray "></i>
                            จัดการข้อมูล
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="navbarDataDropdown">
                            <li>
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('settings.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('settings.index') }}">
                                    <i class="fas fa-university me-2 text-gray"></i>
                                    ข้อมูลมหาวิทยาลัย
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('departments.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('departments.index', []) ?? '#' }}">
                                    <i class="fas fa-building me-2 text-gray"></i>
                                    ข้อมูลสาขา/ภาควิชา
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('positions.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('positions.index', []) ?? '#' }}">
                                    <i class="fas fa-user-tie me-2 text-gray"></i>
                                    ข้อมูลตำแหน่ง
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- การประเมิน -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->routeIs('evaluations.*') ? 'active' : '' }}"
                            href="#">
                            <i class="fas fa-clipboard-check me-2 text-gray"></i>
                            การประเมิน
                        </a>
                    </li>

                    <!-- รายงาน -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                            href="#">
                            <i class="fas fa-chart-bar me-2 text-gray"></i>
                            รายงาน
                        </a>
                    </li>

                    <!-- จัดการระบบ Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle 
                           {{ request()->routeIs(['quality-scores.*']) ? 'active' : '' }}"
                            href="#" id="navbarSystemDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-2 text-gray"></i>
                            จัดการระบบ
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="navbarSystemDropdown">
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="#">
                                    <i class="fas fa-users me-2 text-gray"></i>
                                    จัดการผู้ใช้
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('quality-scores.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('quality-scores.index') }}">
                                    <i class="fas fa-star me-2 text-gray"></i>
                                    คะแนนคุณภาพ
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="#">
                                    <i class="fas fa-tools me-2 text-gray"></i>
                                    ตั้งค่าระบบ
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="#">
                                    <i class="fas fa-download me-2 text-gray"></i>
                                    สำรองข้อมูล
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="#">
                                    <i class="fas fa-sign-out-alt me-2 text-gray"></i>
                                    ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container" style="margin-top: 100px;">
        <div class="main-container fade-in-up">
            <div class="content-area">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="footer-custom">
                <div class="container">
                    <p class="mb-0">
                        ระบบประเมินบุคลากร © {{ date('Y') }} | พัฒนาด้วย Laravel
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
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

    @yield('scripts')
</body>

</html>
