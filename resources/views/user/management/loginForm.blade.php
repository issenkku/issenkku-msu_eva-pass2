<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">เข้าสู่ระบบ</h2>

        <form id="loginForm">
            <div class="mb-4">
                <label for="employee_id" class="block">รหัสพนักงาน</label>
                <input type="text" id="employee_id" name="employee_id" class="w-full border px-3 py-2 rounded" required>
            </div>

            <div class="mb-4">
                <label for="password" class="block">รหัสผ่าน</label>
                <input type="password" id="password" name="password" class="w-full border px-3 py-2 rounded" required>
            </div>

            <div id="error" class="text-red-500 mb-2 hidden"></div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">เข้าสู่ระบบ</button>
        </form>
    </div>

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
                    window.location.href = response.data.redirect || '/users';
                })
                .catch(error => {
                    const message = error.response?.data?.message || 'เข้าสู่ระบบไม่สำเร็จ';
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                });
        });
    </script>
</body>
</html>
