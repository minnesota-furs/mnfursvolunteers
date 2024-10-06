<x-app-layout>
    <div class="py-6">
            <div class="text-center">
                <x-heroicon-o-bug-ant class="w-16 inline"/>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Internal Server Error</h1>
                <p class="mt-6 text-base leading-7 text-gray-600">Something went wrong on our end. Please try again later.</p>
                <p class="mt-6 text-base leading-7 text-gray-600">If the issue persists, please contact support with the following details:</p>
                <ul>
                    <li><strong>Error Code:</strong> {{ $errorCode ?? 'Unknown' }}</li>
                    <li><strong>Timestamp:</strong> {{ $timestamp ?? now()->toDateTimeString() }}</li>
                    <li><strong>Reference ID:</strong> {{ $referenceId ?? 'N/A' }}</li>
                </ul>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                  <a href="{{ url()->previous() }}" class="rounded-md bg-brand-green px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Go back</a>
                </div>
              </div>
    </div>
</x-app-layout>

