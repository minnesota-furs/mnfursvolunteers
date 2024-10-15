<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Department: ') }} {{$department->name}}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form action="{{ route( 'departments.update', $department->id ) }}" id="department" method="POST">
                @csrf
                @method('PATCH')

                <!-- Name -->
                <div>
                    <div style="display: flex; direction: column">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-required-asterisk/>
                    </div>
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $department->name)" placeholder="Department of XXXXX" required autofocus autocomplete="name" />
                    <x-form-validation for="name" />
                    <br>
                </div>

                <!-- Description -->
                <div>
                    <div style="display: flex; direction: column">
                        <x-input-label for="description" :value="__('Description')" />
                        <x-required-asterisk/>
                    </div>
                    <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $department->description)" placeholder="This department does XXXXXXXXX" required autocomplete="description" />
                    <x-form-validation for="description" />
                    <br>
                </div>
                
                <!-- Sector ID -->
                <div>
                    <div style="display: flex; direction: column">
                        <x-input-label for="sector_id" :value="__('Associated Sector')" />
                        <x-required-asterisk/>
                    </div>
                    <x-select-input name="sector_id" id="sector_id" class="block w-64 text-sm" :value="old('sector_id', $department->sector_id)">
                        <option class="text-gray-400" value="">-None-</option>
                        @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ $sector->id == $department->sector_id ? 'selected' : '' }}>
                            {{ $sector->name }}
                        </option>
                        @endforeach
                    </x-select-input>
                    <x-form-validation for="sector_id" />
                    <br>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-4">
                    <x-primary-button id="submit">{{ __('Modify Department') }}</x-primary-button>
                </div>
                <!-- Form fields -->
            </form>

        </div>
    </div>
</x-app-layout>
