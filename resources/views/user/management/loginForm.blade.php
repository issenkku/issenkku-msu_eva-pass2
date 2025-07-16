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
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-300 px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8">
        <div class="flex justify-center mb-4">
            <img src="/favicon-msu.png" alt="MSU Logo" class="h-28 w-28 object-contain" />
        </div>
        <h2 class="text-2xl font-extrabold text-gray-800 mb-6 text-center">เข้าสู่ระบบ</h2>

        <form id="loginForm" class="space-y-5">
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">รหัสพนักงาน</label>
                <input type="text" id="employee_id" name="employee_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="กรอกรหัสพนักงาน" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                <input type="password" id="password" name="password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="กรอกรหัสผ่าน" required>
            </div>

            <div id="error" class="text-red-500 text-sm hidden"></div>

            <div class="flex justify-between items-center">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                    ลืมรหัสผ่าน?
                </a>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                เข้าสู่ระบบ
            </button>
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
                    window.location.href = response.data.redirect;
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
