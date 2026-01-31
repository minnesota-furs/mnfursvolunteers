<x-app-layout>

    <x-slot name="header">
        {{ __('Create New Department') }}
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form class="pb-5" action="{{ route('departments.store') }}" method="POST">
                @csrf
                @method('post')

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="name" id="name" placeholder="Communications" :value="old('name')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="notes" rows="4" name="description" class="block w-full text-sm">{{ old('description') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                <!-- Associated Sector ID -->
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Sector</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-select-input name="sector_id" id="sector_id" class="block w-64 text-sm">
                            <option class="text-gray-400" value="">-None-</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                            @endforeach
                        </x-select-input>
                        <x-form-validation for="sector_id" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0" x-data="{ 
                    search: '', 
                    users: {{ $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'checked' => false])->toJson() }},
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
                    <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
