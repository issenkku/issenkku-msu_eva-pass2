{{-- styles กลางของ layout หลัก --}}
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

    /* Tailwind preflight removes list markers; restore them in rich text. */
    .note-editor .note-editable ul,
    .support-criteria-rich-text ul {
        list-style-type: disc;
        padding-left: 1.5rem;
    }

    .note-editor .note-editable ol,
    .support-criteria-rich-text ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
    }

    .note-editor .note-editable li,
    .support-criteria-rich-text li {
        display: list-item;
    }

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

    .app-background-shell {
        background-image:
            linear-gradient(180deg, #ffffff 0%, #ffffff 42%, rgba(255, 255, 255, 0.78) 55%, rgba(255, 255, 255, 0.08) 70%, rgba(255, 255, 255, 0) 100%),
            var(--app-background-image, url("{{ asset('images/workload-background.jpg') }}"));
        background-position: center top, center bottom;
        background-repeat: no-repeat;
        background-size: 100% 100%, 100% auto;
        background-attachment: fixed;
    }

    .app-background-shell.is-white-background {
        background: #ffffff !important;
    }

    .app-background-shell > main {
        background: transparent;
    }

    .app-background-shell > main > .bg-gray-50,
    .app-background-shell > main > .bg-gradient-to-r {
        background: transparent !important;
    }

    :root {
        --navbar-bg: #0f172a;
        --navbar-border: #334155;
        --navbar-text: #e2e8f0;
        --navbar-text-strong: #f8fafc;
        --navbar-hover-bg: #1e293b;
        --navbar-active-bg: #334155;
        --navbar-shadow: rgba(15, 23, 42, 0.22);
    }

    .header-shell {
        width: 100%;
        max-width: none;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }

    .header-shell > nav {
        margin-left: auto;
    }

    .header-shell .nav {
        justify-content: flex-end;
    }

    /* Custom Navbar */
    .navbar-custom {
        background: var(--navbar-bg) !important;
        border-bottom: 1px solid var(--navbar-border);
        box-shadow: 0 8px 20px var(--navbar-shadow);
        padding: 15px 0;
    }

    .navbar-brand-custom {
        font-weight: 600;
        font-size: 1.5rem;
        color: var(--navbar-text-strong) !important;
        text-decoration: none;
    }

    .navbar-brand-custom:hover {
        color: #cbd5e1 !important;
    }

    .nav-link-custom {
        color: var(--navbar-text) !important;
        font-weight: 500;
        padding: 10px 20px !important;
        border-radius: 8px;
        transition: all 0.2s ease;
        margin: 0 3px;
    }

    .desktop-nav {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-link-custom:hover {
        background: var(--navbar-hover-bg);
        color: var(--navbar-text-strong) !important;
    }

    .nav-link-custom.active {
        background: var(--navbar-active-bg);
        color: var(--navbar-text-strong) !important;
    }

    nav.d-none.d-xl-block a.nav-link[href="/evaluatee-dashboard"] {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    nav.d-none.d-xl-block a.nav-link[href="/evaluatee-dashboard"]::before {
        content: "\f4fc";
        font: var(--fa-font-solid);
    }

    .header-user-link {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
        flex-wrap: nowrap;
    }

    .header-user-link::after {
        margin-left: 0.1rem;
        flex: 0 0 auto;
    }

    .header-user-link span {
        display: inline-block;
        line-height: 1;
    }

    .header-user-avatar {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        object-fit: cover;
        border: 1px solid rgba(255, 255, 255, 0.28);
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08);
        background: #ffffff;
    }

    .header-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.9rem;
        flex: 0 0 auto;
        margin: 0;
        white-space: nowrap;
    }

    .header-brand::before {
        content: "";
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        flex-shrink: 0;
        background: #ffffff url('{{ asset('favicon-msu.png') }}?v=1') center/cover no-repeat;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.14);
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
        background: var(--navbar-hover-bg);
        color: var(--navbar-text-strong);
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
        margin-left: auto;
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
        border-radius: 8px;
        font-size: 1.5rem;
        color: #495057;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 4px;
    }

    .mobile-menu-close:hover {
        background: #f3f4f6;
        color: #111827;
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
    @media (max-width: 1439px) {
        .app-background-shell > main.p-6 {
            padding: 1.25rem !important;
        }

        .header-shell {
            gap: 1rem;
        }

        .header-brand {
            gap: 0.65rem;
            font-size: 1rem !important;
        }

        .header-brand::before {
            width: 40px;
            height: 40px;
        }

        .card,
        .main-container {
            border-radius: 8px;
        }

        .card-body,
        .card-header {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .rounded-2xl.p-6,
        .rounded-xl.p-6 {
            padding: 1.25rem !important;
        }
    }

    @media (max-width: 1199px) {
        .app-background-shell > main.p-6 {
            padding: 1rem !important;
        }

        .header-shell {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .header-brand {
            min-width: 0;
            white-space: normal;
            line-height: 1.25;
        }

        .header-brand::before {
            width: 36px;
            height: 36px;
        }

        .container {
            max-width: 100%;
        }

        .card,
        .main-container {
            margin-left: 0;
            margin-right: 0;
        }

        .btn {
            min-height: 40px;
            padding: 0.5rem 0.85rem;
        }

        .evaluation-list-card {
            padding: 1.25rem !important;
        }

        .evaluation-list-header {
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }

        .evaluation-list-toolbar {
            align-items: stretch !important;
            flex-direction: column !important;
            gap: 0.75rem !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .evaluation-list-toolbar .search-bar-form {
            width: 100%;
            gap: 0.5rem;
            justify-content: flex-start;
        }

        .evaluation-list-toolbar .search-bar-form > :not([hidden]) ~ :not([hidden]) {
            margin-left: 0 !important;
        }

        .evaluation-list-toolbar .search-bar-form > .relative {
            flex: 1 1 auto;
            min-width: 0;
        }

        .evaluation-list-toolbar .search-bar-input {
            width: 100% !important;
        }

        .evaluation-list-toolbar .search-bar-submit {
            flex: 0 0 132px;
        }

        .evaluation-toolbar-actions {
            width: 100%;
            justify-content: flex-start !important;
            gap: 0.6rem !important;
        }

        .evaluation-toolbar-actions .export-button-form {
            flex: 0 1 auto;
        }

        .evaluation-toolbar-actions .export-button-link {
            width: auto;
            justify-content: center;
            white-space: nowrap;
        }

        .evaluation-toolbar-actions .filter-badge-single {
            width: 12rem;
        }

        .table-responsive,
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (max-width: 1121px) {
        .desktop-nav {
            display: none !important;
        }

        .mobile-menu-btn {
            display: block !important;
        }
    }

    @media (max-width: 768px) {
        .app-background-shell > main.p-6 {
            padding: 0.875rem !important;
        }

        .header-shell {
            min-height: 58px;
        }

        .header-brand {
            max-width: calc(100vw - 5rem);
            font-size: 0.92rem !important;
        }

        .header-brand::before {
            width: 32px;
            height: 32px;
        }

        .card,
        .main-container,
        .rounded-2xl,
        .rounded-xl {
            border-radius: 0.75rem !important;
        }

        .card-body,
        .card-header,
        .rounded-2xl.p-6,
        .rounded-xl.p-6 {
            padding: 1rem !important;
        }

        .btn,
        button[type="submit"],
        button[data-dashboard-reset-filters] {
            min-height: 42px;
        }

        .evaluation-list-card {
            padding: 1rem !important;
        }

        .evaluation-list-header {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .evaluation-list-toolbar .search-bar-form {
            flex-wrap: wrap;
        }

        .evaluation-list-toolbar .search-bar-form > .relative {
            flex-basis: 100%;
        }

        .evaluation-list-toolbar .search-bar-submit {
            flex: 1 1 100%;
            width: 100%;
        }

        .evaluation-toolbar-actions {
            flex-direction: column;
        }

        .evaluation-toolbar-actions .export-button-form,
        .evaluation-toolbar-actions .export-button-link,
        .evaluation-toolbar-actions .filter-badge-single {
            width: 100%;
        }

        .evaluation-toolbar-actions .export-button-link {
            min-height: 42px;
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
