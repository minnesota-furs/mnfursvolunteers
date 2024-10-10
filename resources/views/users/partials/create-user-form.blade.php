<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('users.store') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Blue Folf" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" placeholder="user@domain.extension" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- User is Active -->
        <div>
            <x-input-label for="active" :value="__('User is Active')" />
            <x-checkbox-input id="active" name="active" checked />
            <x-input-error class="mt-2" :messages="$errors->get('active')" />
        </div>

        <!-- Grant Admin Privileges -->
        <div>
            <x-input-label for="admin" :value="__('Grant Admin Privileges')" />
            <x-checkbox-input id="admin" name="admin" unchecked />
            <x-input-error class="mt-2" :messages="$errors->get('admin')" />
        </div>

        <!-- User Notes -->
        <div>
            <x-input-label for="notes" :value="__('User Notes')" />
            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full"/>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password" placeholder="XXXXXXXX" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" placeholder="XXXXXXXX" name="password_confirmation" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- Primary Department ID -->
        <div>
            <x-input-label for="primary_dept_id" :value="__('Department ID')" />
            <x-number-input id="primary_dept_id" name="primary_dept_id" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('primary_dept_id')" />
        </div>

        <!-- Primary Sector ID -->
        <div>
            <x-input-label for="primary_sector_id" :value="__('Sector ID')" />
            <x-number-input id="primary_sector_id" name="primary_sector_id" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('primary_sector_id')" />
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
</section>
