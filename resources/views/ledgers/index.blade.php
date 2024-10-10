<x-app-layout>
    <x-slot name="header">
        {{ __('View All Ledgers') }}
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Ledgers</h1>
                        @if (null !== request('search'))
                            <p class="mt-2 text-sm text-orange-700"><x-heroicon-s-magnifying-glass class="w-4 inline" />
                                Currently showing {{ count($users) }} result(s) for search term: <span
                                    class="underline">{{ request('search') }}</span>.
                                <a class="text-blue-600" href="{{ route('users.index') }}">Clear Search</a>
                        @endif
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <a href="{{ route('ledger.create') }}" type="button"
                            class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                            ledger</a>
                    </div>
                </div>
                <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $ledger->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32 ">
                                            Total Hours</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">End Date
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($ledgers as $ledger)
                                    <tr>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-700" href="{{route('ledger.edit', $ledger->id)}}">{{$ledger->name}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0 text-center">
                                            {{format_hours($ledger->totalVolunteerHours())}}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{$ledger->start_date->format('F j, Y')}}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{$ledger->end_date->format('F j, Y')}}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">

                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="">No ledgers found</p>
                                            <p class="text-xs">(You should create one)</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $ledger->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-slot name="right">
        <p class="py-4 text-justify">A Fiscal Ledger represents a specific reporting period, such as a fiscal year, within which an
            organization's activities are tracked and recorded. In the context of volunteer
            hours, a fiscal ledger defines a set timeframe (like "Fiscal Year 2023-24") during which volunteer
            contributions are logged. This allows organizations to group volunteer hours by specific periods for more
            structured reporting, analysis, and accountability.</p>
        <p class="py-4 text-justify">Each time a volunteer logs hours, the volunteer's contribution is linked to a Fiscal Ledger.
            This connection helps in organizing volunteer efforts within distinct reporting periods.</p>
    </x-slot>
</x-app-layout>
