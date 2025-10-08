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
    <style>
        body { background: #f3f4f6; }
    </style>
    @vite([])
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8">
        <div class="flex justify-center mb-4">
            <img src="/favicon-msu.png" alt="MSU Logo" class="h-24 w-24 object-contain" />
        </div>
        <h2 class="text-2xl font-extrabold text-gray-800 mb-2 text-center">ลืมรหัสผ่าน</h2>
        <p class="text-sm text-gray-600 text-center mb-6">กรอกอีเมลที่ลงทะเบียนไว้เพื่อรับลิงก์รีเซ็ตรหัสผ่าน</p>

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
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="off"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="email@example.com">
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                ส่งลิงก์รีเซ็ตรหัสผ่าน
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>

