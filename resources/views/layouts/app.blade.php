<!DOCTYPE html>
<html class="h-full bg-gray-100 dark:bg-gray-900" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
    <script>
        // yes i know this is redundant but we need it here to make sure it has priority.
        // without it, there will be a brief moment where the light theme flashes on the screen before switching to dark mode. this is annoying.
        // everything this does and more is included in `/resources/js/darkmode.js`
        if (localStorage.getItem('theme') === 'dark')
        {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body class="min-h-full">
    @if (filter_var(env('DEMO', false), FILTER_VALIDATE_BOOLEAN))
        <div class="pointer-events-none fixed left-0 top-0 z-50 h-24 w-24 overflow-hidden">
            <a href="{{ route('demo') }}" class="pointer-events-auto absolute left-[-38px] top-[14px] w-40 -rotate-45 bg-gradient-to-r from-purple-500 via-fuchsia-500 to-indigo-500 text-center text-xs font-bold tracking-widest text-white shadow-lg" aria-label="Demo information">
                <span class="block py-1">DEMO</span>
            </a>
        </div>
    @endif
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="pt-2 pb-32 shadow-lg dark:bg-gray-800" style="background-color: {{ app_setting('primary_color', '#10b981') }};">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="py-10">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        @if (isset($actions))
                        <div class="float-right flex gap-2 print:hidden">
                            {{ $actions }}
                        </div>
                        @endif
                        
                        <h1 class="text-3xl font-bold tracking-tight text-white">{{ $header }}</h1>
                    </div>
                </header>
            @else
                <header class="py-4"></header>
            @endif
        </div>

        <!-- Page Heading -->
        @if (isset($postHeader))
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:max-w-7xl lg:px-8 -mt-40 mb-32 pb-6">
                {{ $postHeader }}
            </div>
        @endif

        <!-- Page Content -->
        <main class="-mt-32 pb-8">
        @if (isset($right))
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="sr-only">{{ $header ?? 'Lorem' }}</h1>
                <!-- Main 3 column grid -->
                <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8">
                    <!-- Left column -->
                    <div class="grid grid-cols-1 gap-4 lg:col-span-2">
                        <section aria-labelledby="section-1-title">
                            <h2 class="sr-only" id="section-1-title">Section title</h2>
                            <div class="rounded-lg bg-white dark:bg-slate-900 bg-opacity-90 dark:bg-opacity-75 backdrop-blur-md dark:backdrop-blur-md shadow">
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
                            <div class="rounded-lg bg-white dark:bg-slate-900 bg-opacity-90 dark:bg-opacity-75 backdrop-blur-md dark:backdrop-blur-md shadow">
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
                    <div class="overflow-visible rounded-lg bg-white dark:bg-slate-900 bg-opacity-90 dark:bg-opacity-75 backdrop-blur-md dark:backdrop-blur-md dark:text-white px-5 py-6 shadow sm:px-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        @endif
        @include('layouts.footer')
    </div>
    <!-- Global notification live region, render this permanently at the end of the document -->
    @if(session('success'))
    <div id="toast-notification" aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
        <!--
            Notification panel, dynamically insert this into the live region when it needs to be displayed

            Entering: "transform ease-out duration-300 transition"
            From: "translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            To: "translate-y-0 opacity-100 sm:translate-x-0"
            Leaving: "transition ease-in duration-100"
            From: "opacity-100"
            To: "opacity-0"
        -->
        <div class="pointer-events-auto w-full max-w-sm rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600">
            <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Operation Complete!</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    {!! is_array(session('success')) ? session('success')['message'] : session('success') !!}
                </p>
                @if(is_array(session('success')) && isset(session('success')['action_text']) && isset(session('success')['action_url']))
                    <a href="{{ session('success')['action_url'] }}" class="mt-1 text-sm text-blue-500 dark:text-blue-400">
                        {{ session('success')['action_text'] }}
                    </a>
                @endif
                </div>
                <div class="ml-4 flex flex-shrink-0">
                <button type="button" onclick="closeToastNow()" class="inline-flex rounded-md bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    @endif

    @stack('scripts')
    <script>
        function closeToastNow() {
            hideToast(100); // Close immediately
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Select the toast notification
            const toast = document.getElementById('toast-notification');

            // Check if the toast exists (i.e., if there's a session message)
            if (toast) {
                // Set timeout to hide the toast after 6 seconds
                setTimeout(function () {
                    hideToast(500); // Close over 0.5 seconds
                }, 10000);
            }
        });

        function hideToast(duration) {
            const toast = document.getElementById('toast-notification');

            if (!toast) return; // Ensure the element exists before operating

            // Remove visible classes and add transition classes for fade out
            toast.classList.remove('opacity-100', 'scale-100');
            toast.style.transitionDuration = `${duration}ms`; // Set dynamic transition duration
            toast.classList.add('transition', 'ease-in', 'opacity-0');

            // Wait for the transition to finish before hiding it completely
            setTimeout(() => {
                toast.classList.add('hidden'); // Add a 'hidden' class to hide the toast
            }, duration);
        }
    </script>
</body>

</html>
