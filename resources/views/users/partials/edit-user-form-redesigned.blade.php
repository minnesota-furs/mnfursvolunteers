<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('users.update', $user->id) }}" id="user-form" method="post" class="space-y-8">
            @csrf
            @method('patch')
            
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                {{-- User Information Card --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">User Information</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Basic user account and contact details</p>
                        </div>
                        
                        <div class="mt-6 space-y-6">
                            {{-- Display Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Display Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The name displayed publicly to other users</p>
                            </div>

                            {{-- Legal Names --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="first_name" :value="__('Legal First Name')" />
                                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" autocomplete="given-name" />
                                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                                </div>
                                <div>
                                    <x-input-label for="last_name" :value="__('Legal Last Name')" />
                                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" autocomplete="family-name" />
                                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                                </div>
                            </div>

                            {{-- Email --}}
                            <div>
                                <x-input-label for="email" :value="__('Email Address')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            {{-- Status and Admin Type --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="active" :value="__('Account Status')" />
                                    <select id="active" name="active" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm" required>
                                        <option value="1" {{ old('active', $user->active) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('active', $user->active) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('active')" />
                                </div>
                                <div>
                                    <x-input-label for="admin" :value="__('User Type')" />
                                    <select id="admin" name="admin" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm" required>
                                        <option value="0" {{ old('admin', $user->admin) == 0 ? 'selected' : '' }}>Standard User</option>
                                        <option value="1" {{ old('admin', $user->admin) == 1 ? 'selected' : '' }}>Administrator</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('admin')" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Administrators have full access to user management and permissions
                                    </p>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <x-input-label for="notes" :value="__('Public Notes')" />
                                <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm" placeholder="Add any public notes about this user...">{{ old('notes', $user->notes ?? '') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    These notes are visible to all users and should not contain sensitive information
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Role & Organization Card --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Role & Organization</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Department assignments and organizational structure</p>
                        </div>

                        <div class="mt-6 space-y-6">
                            {{-- Permissions Note --}}
                            <div class="rounded-md bg-blue-50 dark:bg-blue-900 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-100">App Permissions</h3>
                                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-200">
                                            <p>Detailed permissions are managed separately. Visit the user's profile page to assign specific permissions.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Primary Sector --}}
                            <div>
                                <x-input-label for="primary_sector_id" :value="__('Primary Sector')" />
                                <select name="primary_sector_id" id="primary_sector_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 sm:text-sm">
                                    <option value="">— No Primary Sector —</option>
                                    @foreach($sectors as $sector)
                                        <option value="{{ $sector->id }}" {{ old('primary_sector_id', $user->primary_sector_id) == $sector->id ? 'selected' : '' }}>
                                            {{ $sector->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('primary_sector_id')" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    The user's main organizational sector
                                </p>
                            </div>

                            {{-- Departments --}}
                            <div>
                                <x-input-label for="departments" :value="__('Department Assignments')" />
                                <div class="mt-1 max-h-48 overflow-y-auto rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
                                    @foreach ($sectors as $sector)
                                        @if($sector->departments->count() > 0)
                                            <div class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                                <div class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $sector->name }}
                                                </div>
                                                <div class="px-3 py-2 space-y-2">
                                                    @foreach ($sector->departments as $department)
                                                        <label class="flex items-center">
                                                            <input type="checkbox" 
                                                                   name="departments[]" 
                                                                   value="{{ $department->id }}"
                                                                   {{ $user->departments->contains($department->id) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900">
                                                            <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">{{ $department->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('departments')" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Select all departments this user is involved with
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('users.show', $user->id) }}" 
                   class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                    <x-heroicon-m-check class="mr-2 h-4 w-4" />
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>