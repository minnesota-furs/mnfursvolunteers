<!DOCTYPE html>
<html class="h-full bg-gray-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="bg-brand-green dark:bg-gray-800 pt-2 pb-32 shadow-lg">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="py-10">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    @if (isset($actions))
                        <div class="float-right flex gap-2">
                            {{ $actions }}
                        </div>
                    @endif

                        <h1 class="text-3xl font-bold tracking-tight text-white">{{ $header }}</h1>
                    </div>
                </header>
            @endif
        </div>

        <!-- Page Content -->
        @if (isset($right))
            <main class="-mt-32 pb-8">
                <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                    <h1 class="sr-only">{{ $header ?? 'Lorem' }}</h1>
                    <!-- Main 3 column grid -->
                    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8">
                        <!-- Left column -->
                        <div class="grid grid-cols-1 gap-4 lg:col-span-2">
                            <section aria-labelledby="section-1-title">
                                <h2 class="sr-only" id="section-1-title">Section title</h2>
                                <div class="overflow-hidden rounded-lg bg-white shadow">
                                    <div class="p-6">
                                        {{ $slot }}
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Right column -->
                        <div class="grid grid-cols-1 gap-4">
                            <section aria-labelledby="section-2-title">
                                <h2 class="sr-only" id="section-2-title">Section title</h2>
                                <div class="overflow-hidden rounded-lg bg-white shadow">
                                    <div class="p-6">
                                        {{ $right }}
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </main>
        @else
            <main class="-mt-32">
                <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
                    <div class="rounded-lg bg-white dark:bg-slate-900 dark:text-white px-5 py-6 shadow sm:px-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        @endif
        @include('layouts.footer')
    </div>
</body>

</html>
