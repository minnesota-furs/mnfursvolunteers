<x-app-layout>

    @auth

        <x-slot name="header">
            {{ __('Edit Department: ') }} {{$department->name}}
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <form action="{{ route( 'departments.update', $department->id ) }}" id="department" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Name</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <x-text-input class="block w-64 text-sm" type="text" name="name" id="name" :value="old('name', $department->name)" required />
                            <x-form-validation for="name" />
                        </dd>
                    </div>

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Sector</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <x-select-input name="sector_id" id="sector_id" class="block w-64 text-sm" :value="old('sector_id', $department->sector_id)">
                                <option class="text-gray-400" value="">-None-</option>
                                @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ $sector->id == $department->sector_id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                                @endforeach
                            </x-select-input>
                            <x-form-validation for="sector_id" />
                        </dd>
                    </div>

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Description</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <x-textarea-input id="notes" rows="4" name="description" class="block w-full text-sm">{{ old('description', $department->description) }}</x-textarea-input>
                            <x-form-validation for="description" />
                        </dd>
                    </div>

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0" x-data="{ 
                        search: '', 
                        users: {{ $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'checked' => $department->heads->contains($u->id)])->toJson() }},
                        get filteredUsers() {
                            if (!this.search) return this.users;
                            const searchLower = this.search.toLowerCase();
                            return this.users.filter(user => 
                                user.name.toLowerCase().includes(searchLower) || 
                                user.email.toLowerCase().includes(searchLower)
                            );
                        },
                        get selectedCount() {
                            return this.users.filter(u => u.checked).length;
                        }
                    }">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Department Heads</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <div class="mb-3">
                                <input type="text" 
                                    x-model="search" 
                                    placeholder="Search users by name or email..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                <p class="mt-1 text-xs text-gray-500" x-text="`${selectedCount} head(s) selected • Showing ${filteredUsers.length} of ${users.length} users`"></p>
                            </div>
                            <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3">
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                            name="department_heads[]" 
                                            :value="user.id" 
                                            x-model="user.checked"
                                            class="rounded border-gray-300 text-brand-green shadow-sm focus:ring-brand-green">
                                        <span class="ml-2 text-sm" x-text="`${user.name} (${user.email})`"></span>
                                    </label>
                                </template>
                                <div x-show="filteredUsers.length === 0" class="text-sm text-gray-400 py-2">
                                    No users found matching your search.
                                </div>
                            </div>
                            <x-form-validation for="department_heads" />
                        </dd>
                    </div>

                    <div class="py-6 flex justify-end space-x-2">
                        <a type="submit" id="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                        <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                    </div>

                    {{-- <div>
                        <div style="display: flex; direction: column">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-required-asterisk/>
                        </div>
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $department->name)" placeholder="Department of XXXXX" required autofocus autocomplete="name" />
                        <x-form-validation for="name" />
                        <br>
                    </div>

                    <div>
                        <div style="display: flex; direction: column">
                            <x-input-label for="description" :value="__('Description')" />
                        </div>
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $department->description)" placeholder="This department manages the things and stuff" autocomplete="description" />
                        <x-form-validation for="description" />
                        <br>
                    </div>
                    
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

                    <div class="mb-4">
                        <label for="department_head_id" class="block text-sm font-medium text-gray-700">Department Head</label>
                        <select name="department_head_id" id="department_head_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="" {{ is_null($department->department_head_id) ? 'selected' : '' }}>Select a Department Head</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" 
                                    {{ $department->department_head_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                
                    <div class="flex items-center gap-4">
                        <x-primary-button id="submit">{{ __('Modify Department') }}</x-primary-button>
                    </div> --}}
                </form>

            </div>
        </div>

    @endauth
    
</x-app-layout>
