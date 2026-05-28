{{-- ฟังก์ชันพื้นฐานของ layout ที่ต้องพร้อมใช้ทุกหน้า --}}
<script>
    function toggleDropdown(button) {
        const dropdown = button.parentElement;
        const isActive = dropdown.classList.contains('active');
        document.querySelectorAll('.dropdown.active').forEach(d => {
            if (d !== dropdown) {
                d.classList.remove('active');
            }
        });
        dropdown.classList.toggle('active', !isActive);
    }
    function toggleMobileMenu() {
        const overlay = document.getElementById('mobileMenuOverlay');
        const menu = document.getElementById('mobileMenu');

        overlay.classList.add('active');
        menu.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        const overlay = document.getElementById('mobileMenuOverlay');
        const menu = document.getElementById('mobileMenu');

        overlay.classList.remove('active');
        menu.classList.remove('active');
        document.body.style.overflow = '';
        document.querySelectorAll('.mobile-dropdown.active').forEach(d => {
            d.classList.remove('active');
        });
    }
    function toggleMobileDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const icon = dropdown.querySelector('.fa-chevron-down');

        dropdown.classList.toggle('active');
        if (dropdown.classList.contains('active')) {
            icon.style.transform = 'rotate(180deg)';
        } else {
            icon.style.transform = 'rotate(0deg)';
        }
    }
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') {
                return;
            }

            let target = null;
            try {
                target = document.querySelector(href);
            } catch (error) {
                return;
            }

            e.preventDefault();
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        });
    });
    window.addEventListener('scroll', function () {
        const navbar = document.querySelector('.navbar');
        if (navbar && window.scrollY > 50) {
            navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        } else if (navbar) {
            navbar.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.08)';
        }
    });
    window.addEventListener('load', function () {
        document.body.style.opacity = '1';
    });
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
