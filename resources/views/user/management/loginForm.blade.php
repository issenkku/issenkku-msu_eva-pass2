<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-100 to-gray-300 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
        <div class="mb-4 flex justify-center">
            <img src="/favicon-msu.png" alt="MSU Logo" class="h-28 w-28 object-contain">
        </div>

        <h2 class="mb-6 text-center text-2xl font-extrabold text-gray-800">เข้าสู่ระบบ</h2>

        <form id="loginForm" class="space-y-5">
            <div>
                <label for="employee_id" class="mb-1 block text-sm font-medium text-gray-700">รหัสพนักงาน</label>
                <input
                    type="text"
                    id="employee_id"
                    name="employee_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="กรอกรหัสพนักงาน"
                    required
                >
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="กรอกรหัสผ่าน"
                    required
                >
            </div>

            <div id="error" class="hidden text-sm text-red-500"></div>

            <div class="flex items-center justify-between">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                    ลืมรหัสผ่าน?
                </a>
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-600 py-2 font-semibold text-white transition duration-200 hover:bg-blue-700"
            >
                เข้าสู่ระบบ
            </button>
        </form>
    </div>

    @include('user.management.partials.login-form-script')
</body>
</html>
