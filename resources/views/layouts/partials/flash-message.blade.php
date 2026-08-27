{{-- On the profile page, ConCat linking errors are shown as red text next to the
     relevant button instead (see profile/partials/concat-link.blade.php) --}}
@if(session('success') || (session('error') && ! request()->routeIs('profile.edit')))
    @php($isError = ! session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-6 rounded-md border px-4 py-3 {{ $isError ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' }}">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
                @if($isError)
                    <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5 text-red-500 dark:text-red-400" />
                @else
                    <x-heroicon-s-check-circle class="w-5 h-5 shrink-0 mt-0.5 text-green-600 dark:text-green-400" />
                @endif
                <div class="text-sm {{ $isError ? 'text-red-800 dark:text-red-200' : 'text-green-800 dark:text-green-200' }}">
                    @if($isError)
                        {!! is_array(session('error')) ? session('error')['message'] : session('error') !!}
                    @else
                        {!! is_array(session('success')) ? session('success')['message'] : session('success') !!}
                        @if(is_array(session('success')) && isset(session('success')['action_text']) && isset(session('success')['action_url']))
                            <a href="{{ session('success')['action_url'] }}" class="block mt-1 font-medium underline hover:no-underline">
                                {{ session('success')['action_text'] }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            <button type="button" x-on:click="show = false"
                class="shrink-0 {{ $isError ? 'text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300' : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300' }}">
                <span class="sr-only">Dismiss</span>
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    </div>
@endif
