<section>

    <form method="post" action="{{ route('users.store') }}" class="mt-6 space-y-6">
        @csrf
        @method('post')

        <!-- Name -->
        <div>
            <div style="display: flex; direction: column">
                <x-input-label for="name" :value="__('Name')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Blue Folf" required autofocus autocomplete="name" />
            <x-form-validation for="name" />
        </div>

        <!-- Email -->
        <div>
            <div style="display: flex; direction: column">
                <x-input-label for="email" :value="__('Email')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" placeholder="user@domain.extension" required autocomplete="username" />
            <x-form-validation for="email" />
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

        <!-- User Notes -->
        <div>
            <x-input-label for="notes" :value="__('User Notes')" />
            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full"/>
            <x-form-validation for="notes" />
        </div>

        <!-- Password -->
        <div>
            <div style="display: flex; direction: column">
                <x-input-label for="password" :value="__('Password')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="password" name="password" type="text" placeholder="XXXXXXXX" class="mt-1 block w-full" required />
            <x-form-validation for="password" />
        </div>

        <!-- Confirm Password -->
        <div>
            <div style="display: flex; direction: column">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-required-asterisk/>
            </div>
            <x-text-input id="password_confirmation" type="text" placeholder="XXXXXXXX" name="password_confirmation" class="mt-1 block w-full" required />
            <x-form-validation for="password_confirmation" />
        </div>

        <div>
            <p>Use <a href="https://password.link/" style="color:blue">https://password.link/</a> to securely send passwords to users.</p>
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
    </form>
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
