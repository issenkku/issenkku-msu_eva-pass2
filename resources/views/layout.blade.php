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

    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
        }

        /* Custom Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .navbar-brand-custom {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .navbar-brand-custom:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link-custom {
            color: #495057 !important;
            font-weight: 500;
            padding: 10px 20px !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .nav-link-custom:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .nav-link-custom.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white !important;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Custom Toggle Button */
        .navbar-toggler-custom {
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .navbar-toggler-custom:focus {
            box-shadow: none;
        }

        .navbar-toggler-icon-custom {
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Main Container */
        .main-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 20px auto;
            padding: 0;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            min-height: calc(100vh - 200px);
        }

        /* Footer */
        .footer-custom {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Dropdown Menu */
        .dropdown-menu-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 10px;
        }

        .dropdown-item-custom {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
            color: #495057;
        }

        .dropdown-item-custom:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateX(5px);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                border-radius: 15px;
            }

            .content-area {
                padding: 20px;
            }

            .navbar-brand-custom {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a href="{{ route('index') ?? '#' }}" class="navbar-brand navbar-brand-custom">
                <i class="fas fa-chart-line me-2"></i>
                ระบบประเมินบุคลากร
            </a>

            <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon navbar-toggler-icon-custom"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->is('/') ? 'active' : '' }}"
                            href="{{ route('index') ?? '#' }}">
                            <i class="fas fa-home me-2"></i>
                            หน้าแรก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->is('create') ? 'active' : '' }}"
                            href="{{ route('create') ?? '#' }}">
                            <i class="fas fa-plus me-2"></i>
                            เพิ่มข้อมูล
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="navbarDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-2"></i>
                            จัดการ
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item dropdown-item-custom" href="#"><i
                                        class="fas fa-users me-2"></i>จัดการผู้ใช้</a></li>
                            <li><a class="dropdown-item dropdown-item-custom" href="#"><i
                                        class="fas fa-chart-bar me-2"></i>รายงาน</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item dropdown-item-custom" href="#"><i
                                        class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
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
                        <i class="fas fa-heart text-danger me-1"></i>
                        ระบบประเมินบุคลากร © {{ date('Y') }} | พัฒนาด้วย Laravel
                        <i class="fab fa-laravel text-danger ms-1"></i>
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
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Auto-hide navbar on scroll
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }

            lastScrollTop = scrollTop;
        });

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '1';
        });
    </script>

    @yield('scripts')
</body>

</html>
