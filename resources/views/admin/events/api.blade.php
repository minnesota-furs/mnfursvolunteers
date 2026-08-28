<x-app-layout>
    @section('title', 'Event API')
    <x-slot name="header">
        {{ __('API Access') }} - {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @unless ($event->isPublic())
            <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    This event's visibility is <span class="font-mono">{{ $event->visibility }}</span>, not
                    <span class="font-mono">public</span>. The endpoint below will return a 404 until the event's
                    visibility is set to public.
                </p>
            </div>
        @endunless

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg" x-data="{ copied: false }">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Upcoming Shifts</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Returns a JSON list of upcoming shifts for this event. This endpoint is unauthenticated and
                read-only, intended for things like signage boards or embedding on an external website.
            </p>

            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endpoint URL</label>
            <div class="flex gap-2 mb-6">
                <input id="event-api-url" type="text" readonly value="{{ $apiUrl }}"
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-mono text-sm shadow-sm focus:border-brand-green focus:ring-brand-green"
                    onclick="this.select()">
                <button type="button"
                    @click="navigator.clipboard.writeText(@js($apiUrl)); copied = true; setTimeout(() => copied = false, 2000)"
                    class="shrink-0 inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    <template x-if="!copied">
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-o-clipboard class="w-4 h-4" />
                            Copy
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400">
                            <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                            Copied!
                        </span>
                    </template>
                </button>
            </div>

            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Query Parameters</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Parameter</th>
                            <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Type</th>
                            <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Default</th>
                            <th class="py-2 font-medium text-gray-700 dark:text-gray-300">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">limit</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">integer</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">50</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Maximum number of shifts to return. Capped at 100.</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">minutesFromNow</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">integer</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">&mdash;</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Only include shifts starting within this many minutes from now. Omit to include all upcoming shifts.</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">openSlotsOnly</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">boolean</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">false</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">When <span class="font-mono">true</span> (or <span class="font-mono">1</span>), only include shifts that still have open volunteer slots.</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">date</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">date (YYYY-MM-DD)</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">&mdash;</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Only include shifts starting on this calendar date. Useful for multi-day events.</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">tagId</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">integer</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">&mdash;</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Only include shifts tagged with this tag ID (e.g. a department or category).</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">search</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">string</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">&mdash;</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Only include shifts whose name contains this text (case-insensitive substring match).</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">sort</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">string</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">asc</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Order shifts by start time. Either <span class="font-mono">asc</span> (soonest first) or <span class="font-mono">desc</span>.</td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-mono text-gray-800 dark:text-gray-200">descriptionLength</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">integer</td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">&mdash;</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">Truncate each shift's description to this many characters (adds &hellip; if cut off). Omit to return the full description.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mt-6 mb-2">Example Request</h4>
            <div class="rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 mb-2">
                <code class="text-sm text-gray-800 dark:text-gray-200 break-all">GET {{ $apiUrl }}?limit=10&amp;openSlotsOnly=true&amp;sort=asc</code>
            </div>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">
            This endpoint is public and does not require authentication. It only returns data while this event's
            visibility is set to <span class="font-mono">public</span>.
        </p>
        <ul class="text-sm space-y-1 list-disc list-inside text-gray-600 dark:text-gray-400">
            <li>Method: <code>GET</code></li>
            <li>Auth: none</li>
            <li>Visibility required: <code>public</code></li>
        </ul>
    </x-slot>
</x-app-layout>
