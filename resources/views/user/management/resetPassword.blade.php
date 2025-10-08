<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
    @vite([])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8">
        <div class="flex justify-center mb-4">
            <img src="/favicon-msu.png" alt="MSU Logo" class="h-24 w-24 object-contain" />
        </div>
        <h2 class="text-2xl font-extrabold text-gray-800 mb-2 text-center">ตั้งรหัสผ่านใหม่</h2>
        <p class="text-sm text-gray-600 text-center mb-6">กำหนดรหัสผ่านใหม่สำหรับบัญชีของคุณ</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ $email }}" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
                <input id="password" name="password" type="password" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                บันทึกรหัสผ่านใหม่
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>

