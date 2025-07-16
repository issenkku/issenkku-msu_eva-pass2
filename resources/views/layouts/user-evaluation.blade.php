<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกแบบประเมิน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <nav class="sticky top-0 z-50 bg-gray-800 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold">กรอกแบบประเมิน</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-600">Logout</button>
        </form>
    </nav>
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>
