<section>
    <form action="{{ route( 'users.store' ) }}" id="form" method="post">
        @csrf
        {{-- @method('patch') --}}
        <div class="py-4">
            <div class="max-w-7xl mx-auto">
                <div class="">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-4 md:col-span-2">
                                {{-- Start Left Column --}}
                                <div>
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer / User Information</h3>
                                    </div>
                                    <div class="mt-6 border-t border-gray-100">
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Name / Alias</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="name" name="name" placeholder="Mocha Kangaroo" type="text" class="block w-64 text-sm" :value="old('name')" required autofocus autocomplete="name" />
                                                    <x-form-validation for="name" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Password</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="password" name="password" type="text" class="block w-64 text-sm" :value="old('password')" />
                                                    <x-form-validation for="password" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Password Confirmation</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="password_confirmation" name="password_confirmation" type="text" class="block w-64 text-sm" :value="old('password_confirmation')" />
                                                    <x-form-validation for="password_confirmation" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Legal First Name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="first_name" placeholder="Jessie" name="first_name" type="text" class="block w-64 text-sm" :value="old('first_name')" autofocus autocomplete="first_name" />
                                                    <x-form-validation for="first_name" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Legal Last Name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="last_name" name="last_name" placeholder="Anderson" type="text" class="block w-64 text-sm" :value="old('last_name')" autofocus autocomplete="last_name" />
                                                    <x-form-validation for="last_name" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Personal Email address</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="email" name="email" type="email" placeholder="mocharoo@gmail.com" class="block w-64 text-sm" :value="old('email')" required autocomplete="email" />
                                                    <x-form-validation for="email" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="active" name="active" class="block text-sm" required>
                                                        <option value="1" selected>Active</option>
                                                    </x-select-input>
                                                    <x-form-validation for="active" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('active')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">User Type</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="admin" name="admin" class="block text-sm" required>
                                                        <option value="0" {{ old('admin') == 0 ? 'selected' : '' }}>User</option>
                                                        <option value="1" {{ old('admin') == 1 ? 'selected' : '' }}>Admin</option>
                                                    </x-select-input>
                                                    <x-form-validation for="admin" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('admin')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Notes</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes' ?? '') }}</x-textarea-input>
                                                    <x-form-validation for="notes" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-4 md:col-span-2">
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
                                                    <x-select-input name="primary_sector_id" id="primary_sector_id" class="block text-sm" :value="old('primary_sector_id')">
                                                        <option class="text-gray-400" value="" {{ old('primary_sector_id') == null ? 'selected' : '' }}>-None-</option>
                                                        @foreach($sectors as $sector)
                                                            <option value="{{ $sector->id }}" {{ old('primary_sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                                        @endforeach
                                                    </x-select-input>
                                                    <x-input-error class="mt-2" :messages="$errors->get('primary_sector_id')" />
                                                </dd>
                                            </div>
                                            {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Dept</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm"> <!-- required> -->
                                                        <option class="text-gray-400" value="" {{ old('primary_department_id', $user->primary_department_id) == null ? 'selected' : '' }}>-None-</option>
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
                                            </div> --}}

                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Departments (BETA)</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <select 
                                                        name="departments[]" 
                                                        id="departments" 
                                                        size="16"
                                                        multiple 
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">
                                                        @foreach ($sectors as $sector)
                                                            <optgroup label="{{ $sector->name }}" class="font-bold text-gray-700">
                                                                @foreach ($sector->departments as $department)
                                                                    <option value="{{ $department->id }}">
                                                                        {{ $department->name }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        Hold down the Ctrl (Windows) or Command (Mac) key to select multiple departments.
                                                    </p>
                                                </dd>
                                                <x-form-validation for="primary_dept_id" />
                                                <x-input-error class="mt-2" :messages="$errors->get('primary_dept_id')" />
                                            </div>




                                            {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Total Hours</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    {{$user->totalVolunteerHours()}} hours
                                                </dd>
                                            </div> --}}
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
    </form>
    {{-- <form method="post" action="{{ route('users.store') }}" class="mt-6 space-y-6">
        @csrf
        @method('post')

        <!-- Name -->
        <div>
            <div class="flex flex-row">
                <x-input-label for="name" :value="__('Name')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Blue Folf" required autofocus autocomplete="name" />
            <x-form-validation for="name" />
        </div>

        <!-- Email -->
        <div>
            <div class="flex flex-row">
                <x-input-label for="email" :value="__('Email')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" placeholder="user@domain.extension" required autocomplete="username" />
            <x-form-validation for="email" />
        </div>

        <!-- User Notes -->
        <div>
            <x-input-label for="notes" :value="__('User Notes')" />
            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full"/>
            <x-form-validation for="notes" />
        </div>

        <!-- User is Active -->
        <div>
            <x-input-label for="active" :value="__('Active Status')" />
            <x-select-input id="active" name="active" class="block text-sm" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </x-select-input>
            <x-form-validation for="active" />
        </div>

        <!-- Grant Admin Privileges -->
        <div>
            <x-input-label for="admin" :value="__('User Type')" />
            <x-select-input id="admin" name="admin" class="block text-sm" required>
                <option value="0">User</option>
                <option value="1">Admin</option>
            </x-select-input>
            <x-form-validation for="admin" />
        </div>

        <hr>

        <!-- Primary Sector ID -->
        <div>
            <x-input-label for="primary_sector_id" :value="__('Primary Sector')" />
            <x-select-input name="primary_sector_id" id="primary_sector_id" class="block w-64 text-sm">
                <option class="text-gray-400" value="">-None-</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                @endforeach
            </x-select-input>
            <x-form-validation for="primary_sector_id" />
        </div>

        <!-- Primary Department ID -->
        <div>
            <x-input-label for="password" :value="__('Primary Department')" />
            <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm"> <!-- required> -->
                <option value="">-None-</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">
                        {{ $department->name }}
                    </option>
                @endforeach
                <!-- Options will be populated by JavaScript based on the selected sector -->
            </x-select-input>
            <x-form-validation for="primary_dept_id" />
        </div>


        <hr>

        <!-- Password -->
        <div>
            <div class="flex flex-row">
                <x-input-label for="password" :value="__('Password')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="password" name="password" type="text" placeholder="XXXXXXXX" class="mt-1 block w-full" required />
            <x-form-validation for="password" />
        </div>

        <!-- Confirm Password -->
        <div>
            <div class="flex flex-row">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="password_confirmation" type="text" placeholder="XXXXXXXX" name="password_confirmation" class="mt-1 block w-full" required />
            <x-form-validation for="password_confirmation" />
        </div>

        <div>
            <p>Tip: Use <a href="https://password.link/" class="text-blue-500">https://password.link/</a> to securely send passwords to users.</p>
            <br>
            <b>Make sure to encourage new users to change their account password immediately after signing in!</b>
        </div>

        <hr>

        <!-- Submit Button -->
        <div class="flex items-center gap-4">
            <x-primary-button id="submit">{{ __('Create User') }}</x-primary-button>
            {{session('status')}}

            @if (session('status') === 'user-created')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('User Created.') }}</p>
            @endif
        </div>
    </form> --}}
    <script>
        function generateHexPassword()
        {
            // Check if crypto.getRandomValues is not supported by the user's browser. If not, leave passwords blank (will need to be filled manually)
            if (!window.crypto || !window.crypto.getRandomValues) {
                return "";
            }

            // Generate 8 cryptographically random bytes
            const byteArray = new Uint8Array(8); // 8 bytes = 64 bits = 16 hexadecimal characters
            window.crypto.getRandomValues(byteArray);

            // Convert bytes to hexadecimal string
            let hexString = '';
            for (let i = 0; i < byteArray.length; i++) {
                hexString += byteArray[i].toString(16).padStart(2, '0');
            }

            return hexString;
        }
        const generatedPass = generateHexPassword();
        document.getElementById("password").value = generatedPass;
        document.getElementById("password_confirmation").value = generatedPass;
    </script>
</section>
