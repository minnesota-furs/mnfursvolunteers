<x-app-layout>
    <x-slot name="header">
        {{ __('Create New Department') }}
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form class="pb-5" action="{{ route('departments.store') }}" method="POST">
                @csrf
                @method('post')

                <!-- Name -->
                <div>
                    <div style="display: flex; direction: column">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-required-asterisk/>
                    </div>
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Department of XXXXX" required autofocus />
                    <x-form-validation for="name" />
                </div>

                <!-- Description -->
                <div>
                    <div style="display: flex; direction: column">
                        <x-input-label for="description" :value="__('Description')" />
                        <x-required-asterisk/>
                    </div>
                    <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" placeholder="This department does XXXXXXXXX" />
                    <x-form-validation for="description" />
                </div>

                <!-- Associated Sector ID -->
                <div>
                    <x-input-label for="sector_id" :value="__('Parent Sector')" />
                    <x-select-input name="sector_id" id="sector_id" class="block w-64 text-sm">
                        <option class="text-gray-400" value="">-None-</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </x-select-input>
                    <x-form-validation for="sector_id" />
                </div>

                <div class="py-6">
                    <button type="submit" class="block float-right rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
