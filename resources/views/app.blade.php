<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.app-head-theme-script')
        @include('partials.app-head-theme-style')

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-msu.png') }}?v=1">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-msu.png') }}?v=1">
        <link rel="shortcut icon" href="{{ asset('favicon-msu.png') }}?v=1">
        <link rel="apple-touch-icon" href="{{ asset('favicon-msu.png') }}?v=1">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
