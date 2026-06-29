<script>
    const form = document.getElementById('loginForm');
    const errorDiv = document.getElementById('error');

    if (form && window.axios) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const employeeId = form.employee_id.value;
            const password = form.password.value;

            window.axios.post('/login', { employee_id: employeeId, password })
                .then(response => {
                    localStorage.setItem('token', response.data.token || '');
                    window.location.href = response.data.redirect;
                })
                .catch(error => {
                    const message = error.response?.data?.message || 'เข้าสู่ระบบไม่สำเร็จ';
                    if (errorDiv) {
                        errorDiv.textContent = message;
                        errorDiv.classList.remove('hidden');
                    }
                });
        });
    }
</script>
