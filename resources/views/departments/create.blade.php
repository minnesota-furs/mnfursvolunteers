<x-app-layout>
    <x-slot name="header">
        {{ __('Create New Department') }}
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form class="pb-5" action="{{ route('departments.create') }}" method="POST">
                @csrf

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="name" id="name" :value="old('name')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Start Date</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="date" name="start_date" id="start_date" :value="old('start_date')" required />
                        <x-form-validation for="start_date" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">End Date</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="date" name="end_date" id="end_date" :value="old('end_date')" required />
                        <x-form-validation for="end_date" />
                    </dd>
                </div> --}}

                <div class="py-6">
                    <button type="submit" class="block float-right rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
