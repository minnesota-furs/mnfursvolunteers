<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- OpenGraph Meta Tags -->
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:type" content="{{ $ogType }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" x-data="{ mobileMenuOpen: false }">
        <div class="bg-white">
            <header class="absolute inset-x-0 top-0 z-50">
              <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                  <a href="/" class="-m-1.5 p-1.5">
                    <span class="sr-only">{{ app_name() }}</span>
                    <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="h-12 w-auto">
                  </a>
                </div>
                <div class="flex lg:hidden">
                  <button type="button" @click="mobileMenuOpen = true" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700">
                    <span class="sr-only">Open main menu</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                  </button>
                </div>
                <div class="hidden lg:flex lg:gap-x-12">
                  @feature('job_listings')
                  <a href="{{route('job-listings-public.index')}}" class="text-sm/6 font-semibold text-gray-900">Staff Opportunities</a>
                  @endfeature
                  @feature('volunteer_events')
                  <a href="{{route('vol-listings-public.index')}}" class="text-sm/6 font-semibold text-gray-900">Volunteering</a>
                  @endfeature
                  @php
                    // Check if there are any active elections
                    $hasActiveElections = \App\Models\Election::where('active', true)
                        ->where(function($query) {
                            $query->where(function($q) {
                                $q->where('start_date', '<=', now())
                                  ->where('end_date', '>=', now());
                            })
                            ->orWhere(function($q) {
                                $q->where('nomination_start_date', '<=', now())
                                  ->where('nomination_end_date', '>=', now());
                            })
                            ->orWhere(function($q) {
                                $q->where('end_date', '<', now());
                            });
                        })
                        ->exists();
                  @endphp
                  @if($hasActiveElections)
                    <a href="{{route('elections-public.index')}}" class="text-sm/6 font-semibold text-gray-900">Elections</a>
                  @endif
                </div>
                <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm/6 font-semibold text-gray-900">Goto Dashboard <span aria-hidden="true">&rarr;</span></a>
                @else
                    <a href="{{ route('login') }}" class="text-sm/6 font-semibold text-gray-900">Log in <span aria-hidden="true">&rarr;</span></a>
                @endauth
                </div>
              </nav>
              <!-- Mobile menu, show/hide based on menu open state. -->
              <div x-show="mobileMenuOpen" x-cloak class="lg:hidden" role="dialog" aria-modal="true">
                <!-- Background backdrop, show/hide based on slide-over state. -->
                <div class="fixed inset-0 z-50" @click="mobileMenuOpen = false"></div>
                <div class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                  <div class="flex items-center justify-between">
                    <a href="/" class="-m-1.5 p-1.5">
                      <span class="sr-only">{{ app_name() }}</span>
                      <img class="h-8 w-auto" src="{{ app_logo() }}" alt="{{ app_name() }}">
                    </a>
                    <button type="button" @click="mobileMenuOpen = false" class="-m-2.5 rounded-md p-2.5 text-gray-700">
                      <span class="sr-only">Close menu</span>
                      <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div class="mt-6 flow-root">
                    <div class="-my-6 divide-y divide-gray-500/10">
                      <div class="space-y-2 py-6">
                        @feature('job_listings')
                        <a href="{{route('job-listings-public.index')}}" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Staff Opportunities</a>
                        @endfeature
                        @feature('volunteer_events')
                        <a href="{{route('vol-listings-public.index')}}" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Volunteering</a>
                        @endfeature
                        @if($hasActiveElections)
                          <a href="{{route('elections-public.index')}}" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Elections</a>
                        @endif
                      </div>
                      <div class="py-6">
                        @auth
                          <a href="{{ url('/dashboard') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Goto Dashboard</a>
                        @else
                          <a href="{{ route('login') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Log in</a>
                        @endauth
                      </div>
          <style>
            [x-cloak] { display: none !important; }
          </style>
          
                    </div>
                  </div>
                </div>
              </div>
            </header>
            <main>
              {{ $slot }}
            </main>
            @include('layouts.footer')
          </div>
          
        {{-- <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="w-20 h-auto">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div> --}}
    </body>
</html>
