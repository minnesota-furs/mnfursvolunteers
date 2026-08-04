<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/darkmode.js'])
</head>

<body class="min-h-full">
    <div class="min-h-screen max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold tracking-tight">{{ $header }}</h1>
        {{ $slot }}

        {{-- @include('layouts.footer') --}}
    </div>
</body>
</html>
