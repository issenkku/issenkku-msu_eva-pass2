{{-- ไฟล์มุมมอง: resources/views/user/management/partials/login-form-script.blade.php --}}
<script>
    const form = document.getElementById('loginForm');
    const errorDiv = document.getElementById('error');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const employee_id = form.employee_id.value;
        const password = form.password.value;

        axios.post('/login', { employee_id, password })
            .then(response => {
                localStorage.setItem('token', response.data.token);
                window.location.href = response.data.redirect;
            })
            .catch(error => {
                const message = error.response?.data?.message || 'เข้าสู่ระบบไม่สำเร็จ';
                errorDiv.textContent = message;
                errorDiv.classList.remove('hidden');
            });
    });
</script>
