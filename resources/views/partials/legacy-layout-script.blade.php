{{-- ฟังก์ชันพื้นฐานของ layout แบบ legacy --}}
<script>
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
    window.addEventListener('scroll', function () {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        } else {
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
                const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                dropdownToggle.classList.add('active');
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
