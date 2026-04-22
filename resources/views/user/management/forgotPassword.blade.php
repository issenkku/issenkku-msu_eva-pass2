<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @include('user.management.partials.forgot-password-style')
    @vite([])
</head>
<body class="flex min-h-screen items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
        <div class="mb-4 flex justify-center">
            <img src="/favicon-msu.png" alt="MSU Logo" class="h-24 w-24 object-contain">
        </div>

        <h2 class="mb-2 text-center text-2xl font-extrabold text-gray-800">ลืมรหัสผ่าน</h2>
        <p class="mb-6 text-center text-sm text-gray-600">
            กรอกอีเมลที่ลงทะเบียนไว้เพื่อรับลิงก์รีเซ็ตรหัสผ่าน
        </p>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">อีเมล</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="off"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="email@example.com"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-green-600 py-2 font-semibold text-white transition duration-200 hover:bg-green-700"
            >
                ส่งลิงก์รีเซ็ตรหัสผ่าน
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                กลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>
</body>
</html>
