{{-- ฟังก์ชันพื้นฐานของ layout ที่ต้องพร้อมใช้ทุกหน้า --}}
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
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        });
    });

    // Simplified navbar behavior
    window.addEventListener('scroll', function () {
        const navbar = document.querySelector('.navbar');
        if (navbar && window.scrollY > 50) {
            navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        } else if (navbar) {
            navbar.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.08)';
        }
    });

    // Add loading animation
    window.addEventListener('load', function () {
        document.body.style.opacity = '1';
    });

    // Active dropdown highlight
    document.addEventListener('DOMContentLoaded', function () {
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
    document.addEventListener('click', function (event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event.target)) {
                const bsDropdown = new bootstrap.Dropdown(dropdown.previousElementSibling);
                bsDropdown.hide();
            }
        });
    });
</script>
