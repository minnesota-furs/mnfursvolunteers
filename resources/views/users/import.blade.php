<x-app-layout>
    @auth
        @section('title', 'Users - Import Wizard')
        <x-slot name="header">
            Import Users (CSV)
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <x-users.csv-example />

                <form class="pb-5" {{ route('users.import_post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
    
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Upload CSV</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            {{-- <x-text-input class="block w-64 text-sm" type="text" name="name" id="name" :value="old('name')" required /> --}}
                            <input class="p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <x-form-validation for="csv_file" />
                        </dd>
                    </div>
    
                    <div class="py-6 flex justify-end space-x-2">
                        <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                        <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Start Import</button>
                    </div>
                </form>

            </div>
        </div>
    @endauth
</x-app-layout>
