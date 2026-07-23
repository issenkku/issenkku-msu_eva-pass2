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
    @include('partials.layout-app-styles')
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
</head>

<body class="bg-white font-sans antialiased">
    @php
        $appSetting = \App\Models\Setting\Settings::first();
        $isAdminHomeActive = request()->routeIs('dashboard', 'dashboard.*', 'admin.show');
        $isManagerHomeActive = request()->routeIs('manager.dashboard', 'manager.show');
        $isDirectorHomeActive = request()->routeIs('director.dashboard', 'director.show');
        $isEvaluatorActive = request()->routeIs('evaluator.*');
        $isEvaluateeActive = request()->routeIs('evaluatee.dashboard', 'evaluation.show', 'evaluatee.workload');
        $isUsersActive = request()->routeIs('users.*');
        $isUserLogActive = request()->routeIs('user.management.log*');
        $isUserDataActive = $isUsersActive || $isUserLogActive;
        $isCriteriaConfigActive = request()->routeIs('criteria_config.*');
        $isAssignmentDataActive = request()->routeIs('assignment-data.*');
        $isCriteriaManagementActive = $isCriteriaConfigActive || $isAssignmentDataActive;
        $isWebsiteSettingsActive = request()->routeIs('settings.*');
        $isDepartmentsActive = request()->routeIs('departments.*');
        $isPositionsActive = request()->routeIs('positions.*');
        $isJobLevelActive = request()->routeIs('job-level.*');
        $isSubjectsActive = request()->routeIs('subjects.*');
        $isSettingsActive = $isWebsiteSettingsActive
            || $isDepartmentsActive
            || $isPositionsActive
            || $isJobLevelActive
            || $isSubjectsActive;
        $isProfileActive = request()->routeIs('profile.show', 'profile.edit');
    @endphp
    <div class="min-h-screen bg-gray-50 app-background-shell {{ $appSetting?->use_white_background ? 'is-white-background' : '' }}" style="--app-background-image: url('{{ $appSetting?->background_url ?? asset('images/workload-background.jpg') }}');">
        <!-- Header -->
        <header class="shadow-sm" style="background: #0f172a; border-bottom: 1px solid #334155;">
            <div class="header-shell py-2 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-lg font-semibold text-white header-brand">
                    ระบบประเมินบุคลากร
                </h1>
                <nav class="d-none d-xl-block">
                    <ul class="nav nav-pills align-items-center gap-2">
                        
                        @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร'))
                            <!-- <li class="nav-item">
                                <a class="nav-link text-white " href="/dashboard">แดชบอร์ด</a>
                            </li> -->
                            <li class="nav-item">
                                <a
                                    href="/manager-dashboard"
                                    data-nav-key="manager-home"
                                    @class([
                                        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
                                        'is-active' => $isManagerHomeActive,
                                    ])
                                    @if($isManagerHomeActive) aria-current="page" @endif><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif
                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a
                                    href="/dashboard"
                                    data-nav-key="admin-home"
                                    @class([
                                        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
                                        'is-active' => $isAdminHomeActive,
                                    ])
                                    @if($isAdminHomeActive) aria-current="page" @endif><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif

                        @if(auth()->user() && auth()->user()->hasRole('ผู้ประเมิน'))
                            <li class="nav-item">
                                <a
                                    href="/evaluator-dashboard"
                                    data-nav-key="evaluator"
                                    @class([
                                        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
                                        'is-active' => $isEvaluatorActive,
                                    ])
                                    @if($isEvaluatorActive) aria-current="page" @endif><i class="fas fa-home"></i><span>หน้าประเมินผู้อื่น</span></a>
                            </li>
                        @endif
                        {{-- @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร') && auth()->user()->position && auth()->user()->position->name == 'คณบดี')
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/evaluatee-dashboard">หน้าตรวจประเมิน</a>
                            </li>
                        @endif --}}
                        @if(auth()->user() && auth()->user()->hasRole('กรรมการ'))
                            <li class="nav-item">
                                <a
                                    href="/director-dashboard"
                                    data-nav-key="director-home"
                                    @class([
                                        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
                                        'is-active' => $isDirectorHomeActive,
                                    ])
                                    @if($isDirectorHomeActive) aria-current="page" @endif><i class="fas fa-home"></i><span>หน้าหลัก</span></a>
                            </li>
                        @endif
                        @if(auth()->user() && ($showEvaluateeNavigation ?? false))
                            <li class="nav-item">
                                <a
                                    href="/evaluatee-dashboard"
                                    data-nav-key="evaluatee"
                                    @class([
                                        'nav-link text-white app-nav-link',
                                        'is-active' => $isEvaluateeActive,
                                    ])
                                    @if($isEvaluateeActive) aria-current="page" @endif>หน้าประเมินตนเอง</a>
                            </li>
                        @endif

                        @if(auth()->user() && auth()->user()->hasRole('admin'))
                        <li class="nav-item dropdown">
                             <a
                                data-nav-key="user-data"
                                @class([
                                    'nav-link dropdown-toggle text-white d-flex align-items-center gap-2 app-nav-link',
                                    'is-active' => $isUserDataActive,
                                ])
                                href="#"
                                id="userDataDropdown"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @if($isUserDataActive) aria-current="true" @endif>
                                <i class="fas fa-users"></i>
                                <span>ข้อมูลผู้ใช้</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDataDropdown">
                                <li><a
                                    data-nav-child="users"
                                    @class(['dropdown-item app-dropdown-item', 'is-active' => $isUsersActive])
                                    href="{{ route('users.index') }}"
                                    @if($isUsersActive) aria-current="page" @endif><i class="fas fa-users me-2"></i>จัดการสมาชิก</a></li>
                                <li><a
                                    data-nav-child="user-log"
                                    @class(['dropdown-item app-dropdown-item', 'is-active' => $isUserLogActive])
                                    href="{{ route('user.management.log') }}"
                                    @if($isUserLogActive) aria-current="page" @endif><i class="fas fa-history me-2"></i>ประวัติการเข้าใช้งาน</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a
                                data-nav-key="criteria-management"
                                @class([
                                    'nav-link dropdown-toggle text-white d-flex align-items-center gap-2 app-nav-link',
                                    'is-active' => $isCriteriaManagementActive,
                                ])
                                href="#"
                                id="criteriaManagementDropdown"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @if($isCriteriaManagementActive) aria-current="true" @endif>
                                <i class="fas fa-list-check"></i>
                                <span>จัดการเกณฑ์</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="criteriaManagementDropdown">
                                <li><a
                                    data-nav-child="criteria-config"
                                    @class(['dropdown-item app-dropdown-item', 'is-active' => $isCriteriaConfigActive])
                                    href="/criteria-config"
                                    @if($isCriteriaConfigActive) aria-current="page" @endif><i class="fas fa-sitemap me-2"></i>จัดการโครงสร้างเกณฑ์</a></li>
                                <li><a
                                    data-nav-child="assignment-data"
                                    @class(['dropdown-item app-dropdown-item', 'is-active' => $isAssignmentDataActive])
                                    href="{{ route('assignment-data.index') }}"
                                    @if($isAssignmentDataActive) aria-current="page" @endif><i class="fas fa-calendar-check me-2"></i>จัดการรอบการประเมิน</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a
                                data-nav-key="settings"
                                @class([
                                    'nav-link dropdown-toggle text-white d-flex align-items-center gap-2 app-nav-link',
                                    'is-active' => $isSettingsActive,
                                ])
                                href="#"
                                id="settingsDropdownDesktop"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @if($isSettingsActive) aria-current="true" @endif>
                                <i class="fas fa-cog"></i>
                                <span>ตั้งค่า</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="settingsDropdownDesktop">
                                <li><a data-nav-child="settings-website" @class(['dropdown-item app-dropdown-item', 'is-active' => $isWebsiteSettingsActive]) href="{{ route('settings.index') }}" @if($isWebsiteSettingsActive) aria-current="page" @endif><i class="fas fa-globe me-2"></i>ตั้งค่าเว็บไซต์</a></li>
                                <li><a data-nav-child="departments" @class(['dropdown-item app-dropdown-item', 'is-active' => $isDepartmentsActive]) href="{{ route('departments.index') }}" @if($isDepartmentsActive) aria-current="page" @endif><i class="fas fa-building me-2"></i>ตั้งค่าหน่วยงาน/แผนก</a></li>
                                <li><a data-nav-child="positions" @class(['dropdown-item app-dropdown-item', 'is-active' => $isPositionsActive]) href="{{ route('positions.index') }}" @if($isPositionsActive) aria-current="page" @endif><i class="fas fa-briefcase me-2"></i>ตั้งค่าตำแหน่งงาน</a></li>
                                <li><a data-nav-child="job-level" @class(['dropdown-item app-dropdown-item', 'is-active' => $isJobLevelActive]) href="{{ route('job-level.index') }}" @if($isJobLevelActive) aria-current="page" @endif><i class="fas fa-layer-group me-2"></i>ตั้งค่าระดับตำแหน่งงาน</a></li>
                                <li><a data-nav-child="subjects" @class(['dropdown-item app-dropdown-item', 'is-active' => $isSubjectsActive]) href="{{ route('subjects.index') }}" @if($isSubjectsActive) aria-current="page" @endif><i class="fas fa-book-open me-2"></i>ตั้งค่ารายวิชา</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(auth()->user())
                        <li class="nav-item dropdown">
                            <a
                                data-nav-key="profile"
                                @class([
                                    'nav-link dropdown-toggle text-white header-user-link app-nav-link',
                                    'is-active' => $isProfileActive,
                                ])
                                href="#"
                                id="userDropdown"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @if($isProfileActive) aria-current="true" @endif>
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="header-user-avatar">
                                <span>{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a
                                    data-nav-child="profile"
                                    @class(['dropdown-item app-dropdown-item', 'is-active' => $isProfileActive])
                                    href="/profile"
                                    @if($isProfileActive) aria-current="page" @endif><i class="fas fa-user-cog me-2"></i>ตั้งค่าโปรไฟล์</a></li>
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
                            target="_blank"
                            rel="noopener noreferrer"
                            title="เปิดคู่มือการใช้งาน">
                                <i class="fas fa-book me-2"></i>
                                <span class="d-none d-xxl-inline">คู่มือ</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>

                <button
                    id="mobileMenuToggle"
                    class="mobile-menu-btn d-block d-xl-none"
                    type="button"
                    data-mobile-menu-toggle
                    aria-controls="mobileMenu"
                    aria-expanded="false"
                    aria-label="เปิดเมนู"
                >
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>

        <div class="mobile-menu-overlay" id="mobileMenuOverlay" data-mobile-menu-close></div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <span class="mobile-menu-brand">
                    <span>ระบบประเมินบุคลากร</span>
                </span>
                <button class="mobile-menu-close" type="button" data-mobile-menu-close aria-label="ปิดเมนู">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mobile-menu-content">
                <!-- Role-based Navigation Links -->
                @if(auth()->user() && auth()->user()->hasRole('ผู้บริหาร'))
                    <!-- <a href="/dashboard" class="mobile-nav-item">
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        แดชบอร์ด
                    </a> -->
                    <a
                        href="/manager-dashboard"
                        data-mobile-nav-key="manager-home"
                        @class(['mobile-nav-item', 'is-active' => $isManagerHomeActive])
                        @if($isManagerHomeActive) aria-current="page" @endif>
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าหลัก
                    </a>
                @endif
                
                @if(auth()->user() && auth()->user()->hasRole('ผู้ประเมิน'))
                    <a
                        href="/evaluator-dashboard"
                        data-mobile-nav-key="evaluator"
                        @class(['mobile-nav-item', 'is-active' => $isEvaluatorActive])
                        @if($isEvaluatorActive) aria-current="page" @endif>
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าประเมินผู้อื่น
                    </a>
                @endif

                @if(auth()->user() && auth()->user()->hasRole('กรรมการ'))
                    <a
                        href="/director-dashboard"
                        data-mobile-nav-key="director-home"
                        @class(['mobile-nav-item', 'is-active' => $isDirectorHomeActive])
                        @if($isDirectorHomeActive) aria-current="page" @endif>
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าหลัก
                    </a>
                @endif
                
                @if(auth()->user() && ($showEvaluateeNavigation ?? false))
                    <a
                        href="/evaluatee-dashboard"
                        data-mobile-nav-key="evaluatee"
                        @class(['mobile-nav-item', 'is-active' => $isEvaluateeActive])
                        @if($isEvaluateeActive) aria-current="page" @endif>
                        <i class="fas fa-user-check" style="width: 20px; margin-right: 10px;"></i>
                        หน้าประเมินตนเอง
                    </a>
                @endif
                
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <a
                        href="/dashboard"
                        data-mobile-nav-key="admin-home"
                        @class(['mobile-nav-item', 'is-active' => $isAdminHomeActive])
                        @if($isAdminHomeActive) aria-current="page" @endif>
                        <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
                        หน้าแรก
                    </a>
                    <a
                        href="{{ route('users.index') }}"
                        data-mobile-nav-key="users"
                        @class(['mobile-nav-item', 'is-active' => $isUsersActive])
                        @if($isUsersActive) aria-current="page" @endif>
                        <i class="fas fa-users" style="width: 20px; margin-right: 10px;"></i>
                        จัดการสมาชิก
                    </a>
                    <a
                        href="{{ route('user.management.log') }}"
                        data-mobile-nav-key="user-log"
                        @class(['mobile-nav-item', 'is-active' => $isUserLogActive])
                        @if($isUserLogActive) aria-current="page" @endif>
                        <i class="fas fa-history" style="width: 20px; margin-right: 10px;"></i>
                        ประวัติการเข้าใช้งาน
                    </a>
                    <a
                        href="/criteria-config"
                        data-mobile-nav-key="criteria-config"
                        @class(['mobile-nav-item', 'is-active' => $isCriteriaConfigActive])
                        @if($isCriteriaConfigActive) aria-current="page" @endif>
                        <i class="fas fa-cogs" style="width: 20px; margin-right: 10px;"></i>
                        จัดการโครงสร้างเกณฑ์
                    </a>
                    <a
                        href="{{ route('assignment-data.index') }}"
                        data-mobile-nav-key="assignment-data"
                        @class(['mobile-nav-item', 'is-active' => $isAssignmentDataActive])
                        @if($isAssignmentDataActive) aria-current="page" @endif>
                        <i class="fas fa-tasks" style="width: 20px; margin-right: 10px;"></i>
                        จัดการรอบการประเมิน
                    </a>
                    <!-- Settings Dropdown for Mobile -->
                    <div id="settingsDropdown" @class(['mobile-dropdown', 'active' => $isSettingsActive])>
                        <button
                            data-mobile-nav-key="settings"
                            @class(['mobile-dropdown-toggle', 'is-active' => $isSettingsActive])
                            type="button"
                            data-mobile-dropdown-toggle
                            data-mobile-dropdown-target="settingsDropdown"
                            aria-controls="settingsDropdownMenu"
                            aria-expanded="{{ $isSettingsActive ? 'true' : 'false' }}"
                            @if($isSettingsActive) aria-current="true" @endif>
                            <span>
                                <i class="fas fa-cog" style="width: 20px; margin-right: 10px;"></i>
                                ตั้งค่า
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-300"></i>
                        </button>
                        <div class="mobile-dropdown-content" id="settingsDropdownMenu">
                            <a href="{{ route('settings.index') }}" data-mobile-nav-key="settings-website" @class(['mobile-dropdown-item', 'is-active' => $isWebsiteSettingsActive]) @if($isWebsiteSettingsActive) aria-current="page" @endif>ตั้งค่าเว็บไซต์</a>
                            <a href="{{ route('departments.index') }}" data-mobile-nav-key="departments" @class(['mobile-dropdown-item', 'is-active' => $isDepartmentsActive]) @if($isDepartmentsActive) aria-current="page" @endif>ตั้งค่าหน่วยงาน/แผนก</a>
                            <a href="{{ route('positions.index') }}" data-mobile-nav-key="positions" @class(['mobile-dropdown-item', 'is-active' => $isPositionsActive]) @if($isPositionsActive) aria-current="page" @endif>ตั้งค่าตำแหน่งงาน</a>
                            <a href="{{ route('job-level.index') }}" data-mobile-nav-key="job-level" @class(['mobile-dropdown-item', 'is-active' => $isJobLevelActive]) @if($isJobLevelActive) aria-current="page" @endif>ตั้งค่าระดับตำแหน่งงาน</a>
                            <a href="{{ route('subjects.index') }}" data-mobile-nav-key="subjects" @class(['mobile-dropdown-item', 'is-active' => $isSubjectsActive]) @if($isSubjectsActive) aria-current="page" @endif>ตั้งค่ารายวิชา</a>
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
                <a
                    href="/profile"
                    data-mobile-nav-key="profile"
                    @class(['mobile-nav-item', 'is-active' => $isProfileActive])
                    @if($isProfileActive) aria-current="page" @endif
                    style="padding: 10px 0; border: none;">
                    <i class="fas fa-user-edit" style="width: 20px; margin-right: 10px;"></i>
                    ตั้งค่าโปรไฟล์
                </a>
                <a href="{{ asset('downloads/handbook.pdf') }}"  class="mobile-nav-item" 
                    style="padding: 10px 0; border: none;" 
                    target="_blank"
                    rel="noopener noreferrer"
                    title="เปิดคู่มือการใช้งาน">
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

    @include('partials.layout-app-shell-script')

    @if (session('success'))
        <script>
            // Show success message if needed
        </script>
    @endif
</body>

</html>
