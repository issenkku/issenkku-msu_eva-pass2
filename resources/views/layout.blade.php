<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    <title>@yield('title', 'ระบบประเมินบุคลากร')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('partials.legacy-layout-styles')
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
                    <!-- จัดการข้อมูล Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle 
                           {{ request()->routeIs(['settings.*', 'departments.*', 'positions.*', 'job-level.*']) ? 'active' : '' }}"
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
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('job-level.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('job-level.index', []) ?? '#' }}">
                                    <i class="fas fa-user-tie me-2 text-gray"></i>
                                    ข้อมูลระดับตําแหน่งงาน
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom {{ request()->routeIs('positions.*') ? 'fw-bold' : '' }}"
                                    href="{{ route('positions.index', []) ?? '#' }}">
                                    <i class="fas fa-briefcase me-2 text-gray"></i>
                                    ข้อมูลตำแหน่งงาน
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

    @include('partials.legacy-layout-script')

    @yield('scripts')
</body>

</html>
