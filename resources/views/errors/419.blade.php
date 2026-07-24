<x-guest-layout>
    <div class="py-6">
            <div class="text-center">
                <x-heroicon-o-clock class="w-16 inline"/>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Page Expired</h1>
                <p class="mt-6 text-base leading-7 text-gray-600">Your session timed out, so we couldn't process that. Any information you entered was not saved.</p>
                <p class="mt-6 text-base leading-7 text-gray-600">Please go back and try again. If this keeps happening, make sure cookies are enabled and you're not browsing in Private mode.</p>
                <p class="mt-6 text-base leading-7 text-gray-600">When all else fails, please try using a different browser.</p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                  <a href="{{ url()->previous() }}" class="rounded-md bg-brand-green px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Go back</a>
                </div>
              </div>
    </div>
</x-guest-layout>
