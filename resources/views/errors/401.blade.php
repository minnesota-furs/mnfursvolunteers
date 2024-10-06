<x-app-layout>
    <div class="py-6">
            <div class="max-w-2xl mx-auto text-center">
                <x-heroicon-o-lock-closed class="w-16 inline"/>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Unauthorized Access</h1>
                <p class="mt-6 text-base leading-7 text-gray-600">You are not authorized to view this page or conduct that action.</p>
                <p class="mt-6 text-base leading-7 text-gray-600">If you believe you should be able to access this page or are having issues, please contact support or staff admin to have your user permissions checked.</p>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    @if (Auth::check())
                    <a href="{{ url()->previous() }}" class="rounded-md bg-brand-green px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Go back</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md bg-brand-green px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Log In</a>
                    @endif
                </div>
              </div>
    </div>
</x-app-layout>

