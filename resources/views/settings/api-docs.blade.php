<x-app-layout>
    @section('title', 'API Documentation')
    <x-slot name="header">
        {{ __('API Documentation') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('settings.index') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            &larr; Back to Settings
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Upcoming Shifts</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Returns a JSON list of upcoming shifts for a public event. This endpoint is unauthenticated and
                read-only, intended for things like signage boards or embedding on an external website. Only events
                with visibility set to <span class="font-mono">public</span> are exposed; other events return a 404.
            </p>

            <div class="rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 mb-6">
                <code class="text-sm text-gray-800 dark:text-gray-200">GET /api/events/{event}/shifts/upcoming</code>
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
            <div class="rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 mb-6">
                <code class="text-sm text-gray-800 dark:text-gray-200 break-all">GET /api/events/123/shifts/upcoming?limit=10&amp;minutesFromNow=1440&amp;openSlotsOnly=true&amp;date=2026-07-08&amp;tagId=5&amp;search=registration&amp;sort=asc</code>
            </div>

            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Example Response</h4>
            <pre class="rounded-md bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 text-sm text-gray-800 dark:text-gray-200 overflow-x-auto">{
    "status": "success",
    "data": [
        {
            "id": 456,
            "name": "Registration Desk",
            "description": "Greet guests and hand out badges.",
            "start_time": "2026-07-08T09:00:00-05:00",
            "end_time": "2026-07-08T13:00:00-05:00",
            "max_volunteers": 4,
            "volunteers_signed_up": 2,
            "open_slots": 2,
            "tags": ["Front of House"]
        }
    ]
}</pre>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">
            This endpoint is public and does not require authentication. It only ever returns data for events whose
            visibility is set to <span class="font-mono">public</span>.
        </p>
        <ul class="text-sm space-y-1 list-disc list-inside text-gray-600 dark:text-gray-400">
            <li>Upcoming shifts endpoint: <code>/api/events/{event}/shifts/upcoming</code></li>
            <li>Method: <code>GET</code></li>
            <li>Auth: none</li>
        </ul>
    </x-slot>
</x-app-layout>
