<x-app-layout>
    <x-slot name="header">
        Volunteers for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to All Events
        </a>
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-clock class="w-4 inline"/> Manage Volunteer Slots
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                {{-- <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                    </div>
                </div> --}}
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $shifts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Email</th>
                                        {{-- <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            End Time</th> --}}
                                        {{-- <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Volunteers
                                        </th> --}}
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($volunteers as $volunteer)
                                    <tr class="">
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{-- <a class="text-blue-700" href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}"> --}}
                                                {{$volunteer->name}}
                                            {{-- </a> --}}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-500" href="mailto:{{ $volunteer->name }}<{{ $volunteer->email }}>">{{$volunteer->email}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a href="{{ route('users.show', $volunteer->id) }}" class="text-blue-600 px-2">View Volunteer</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="font-semibold">No signups.</p>
                                            <p class="">Nobody has signed up for slots yet.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $shifts->links() }} --}}
                        </div>
                    </div>
                    
                </div>
                <div class="mt-6">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Quick Copy Email List (BCC)</h1>
                    <div class="py-2">
                    <p class="text-xs text-gray-700">This is all volunteers across all shifts for this event. If you are using this to mass email volunteers, please use the BCC field to protect their privacy.</p>
                    @if($volunteers->count() > 20)
                    <p class="text-xs text-red-500"><span class="font-bold">There are a lot of email recipients</span>, you may want to use a newsletter platform to avoid deliverablity issues due to spam protections.</p>
                    @endif
                    </div>
                    <x-textarea-input id="notes" rows="6" name="description" class="block w-full text-sm" readonly>
                        {{ $volunteers->pluck('email')->join(', ') }}
                    </x-textarea-input>
                    @if($volunteers->count() > 0)
                    <a href="mailto:?bcc={{ urlencode($bccList) }}&body=%0D%0A%0D%0A------%0D%0AYou're getting this email because you signed up to volunteer for {{ $event->name }}. You can manage your slots at {{ route('volunteer.events.show', $event) }}."
                        class="mt-2 inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Email All Volunteers ("Mailto:" Method)
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4 text-justify">Paragraph one.</p>
        <p class="py-4 text-justify">Paragraph two.</p>
    </x-slot> --}}
    {{-- <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script> --}}
</x-app-layout>
