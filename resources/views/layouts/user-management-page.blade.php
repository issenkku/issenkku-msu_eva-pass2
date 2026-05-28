<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การจัดการผู้ใช้งานและสิทธิการเข้าถึง</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    {{-- เมนูนำทาง --}}
    <nav class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold">การจัดการผู้ใช้งานและสิทธิการเข้าถึง</h1>
        {{-- ฟอร์ม --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-600">Logout</button>
        </form>
    </nav>
    {{-- เนื้อหาหลัก --}}
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>

