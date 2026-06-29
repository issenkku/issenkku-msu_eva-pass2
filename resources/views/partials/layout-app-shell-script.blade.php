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
    function setMobileMenuState(isOpen) {
        const overlay = document.getElementById('mobileMenuOverlay');
        const menu = document.getElementById('mobileMenu');
        const toggle = document.getElementById('mobileMenuToggle');

        if (!overlay || !menu) {
            return;
        }

        overlay.classList.toggle('active', isOpen);
        menu.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';

        if (toggle) {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        if (!isOpen) {
            document.querySelectorAll('.mobile-dropdown.active').forEach(d => {
                d.classList.remove('active');
            });
        }
    }
    function toggleMobileMenu() {
        setMobileMenuState(true);
    }
    function closeMobileMenu() {
        setMobileMenuState(false);
    }
    function toggleMobileDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const button = document.querySelector(`[data-mobile-dropdown-target="${dropdownId}"]`);
        const icon = dropdown ? dropdown.querySelector('.fa-chevron-down') : null;

        if (!dropdown) {
            return;
        }

        dropdown.classList.toggle('active');
        if (button) {
            button.setAttribute('aria-expanded', dropdown.classList.contains('active') ? 'true' : 'false');
        }
        if (dropdown.classList.contains('active')) {
            if (icon) {
                icon.style.transform = 'rotate(180deg)';
            }
        } else if (icon) {
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
        const mobileMenuToggle = event.target.closest('[data-mobile-menu-toggle]');
        if (mobileMenuToggle) {
            toggleMobileMenu();
            return;
        }

        const mobileMenuClose = event.target.closest('[data-mobile-menu-close]');
        if (mobileMenuClose) {
            closeMobileMenu();
            return;
        }

        const mobileDropdownToggle = event.target.closest('[data-mobile-dropdown-toggle]');
        if (mobileDropdownToggle) {
            const targetId = mobileDropdownToggle.dataset.mobileDropdownTarget;
            if (targetId) {
                toggleMobileDropdown(targetId);
            }
            return;
        }

        const flashCloseButton = event.target.closest('[data-flash-close]');
        if (flashCloseButton) {
            const message = flashCloseButton.closest('#successMessage, #warningMessage, #errorMessage, #deleteSuccessMessage');
            if (message) {
                message.remove();
            }
            return;
        }

        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target) && !dropdown.previousElementSibling.contains(event.target)) {
                const bsDropdown = new bootstrap.Dropdown(dropdown.previousElementSibling);
                bsDropdown.hide();
            }
        });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    });
</script>
