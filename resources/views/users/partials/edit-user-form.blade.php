<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form action="{{ route( 'users.update', $user->id ) }}" id="form" method="POST">
        @csrf
        @method('post')
        <div class="py-4">
            <div class="max-w-7xl mx-auto">
                <div class="">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                {{-- Start Left Column --}}
                                <div>
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer / User Information</h3>
                                    </div>
                                    <div class="mt-6 border-t border-gray-100">
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Full name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="name" name="name" type="text" class="block w-64 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                                    <x-form-validation for="name" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Email address</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="email" name="email" type="email" class="block w-64 text-sm" :value="old('email', $user->email)" required autocomplete="email" />
                                                    <x-form-validation for="email" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="active" name="active" class="block text-sm" required>
                                                        <option value="1" {{ old('active', $user->active) == 1 ? 'selected' : '' }}>Active</option>
                                                        <option value="0" {{ old('active', $user->active) == 0 ? 'selected' : '' }}>Inactive</option>
                                                    </x-select-input>
                                                    <x-form-validation for="active" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('active')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">User Type</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="admin" name="admin" class="block text-sm" required>
                                                        <option value="0" {{ old('admin', $user->admin) == 0 ? 'selected' : '' }}>User</option>
                                                        <option value="1" {{ old('admin', $user->admin) == 1 ? 'selected' : '' }}>Admin</option>
                                                    </x-select-input>
                                                    <x-form-validation for="admin" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('admin')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Notes</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes', $user->notes ?? '') }}</x-textarea-input>
                                                    <x-form-validation for="notes" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div>
                                {{-- Start Right Column --}}
                                <div>
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900">Role Information</h3>
                                        {{-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Information involving their staff involvement with the group</p> --}}
                                    </div>
                                    <div class="mt-6 border-t border-gray-100">
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Sector</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_sector_id" id="primary_sector_id" class="block text-sm" :value="old('primary_sector_id', $user->primary_sector_id)">
                                                        <option class="text-gray-400" value="" {{ old('primary_sector_id', $user->primary_sector_id) == null ? 'selected' : '' }}>-None-</option>
                                                        @foreach($sectors as $sector)
                                                            <option value="{{ $sector->id }}" {{ old('primary_sector_id', $user->primary_sector_id) == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                                        @endforeach
                                                    </x-select-input>
                                                    <x-input-error class="mt-2" :messages="$errors->get('primary_sector_id')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Dept</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm"> <!-- required> -->
                                                        <option value="">Select Department</option>
                                                        @foreach($departments as $department)
                                                            <option value="{{ $department->id }}" {{ $user->department->id ?? '' == $department->id ? 'selected' : '' }}>
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                        <!-- Options will be populated by JavaScript based on the selected sector -->
                                                    </x-select-input>
                                                </dd>
                                                <x-form-validation for="primary_dept_id" />
                                                <x-input-error class="mt-2" :messages="$errors->get('primary_dept_id')" />
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Total Hours</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    {{$user->totalVolunteerHours()}} hours
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="py-6 flex justify-end space-x-2">
                            <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                            <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
    </form>
</section>
