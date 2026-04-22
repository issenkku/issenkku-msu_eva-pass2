{{-- styles กลางของ layout แบบ legacy --}}
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
