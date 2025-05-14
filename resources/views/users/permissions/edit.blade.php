<x-app-layout>
    <x-slot name="header">
        {{ __('Edit User Permissions: ') }} {{$user->name}}
    </x-slot>

    <x-slot name="actions">
        {{-- @if( Auth::user()->isAdmin() && Auth::user()->id != $user->id )
        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    onclick="return confirm('Are you sure you want to delete this user?');">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
            </button>
        </form>
        @endif
        <a href="{{ url()->previous() }}"
            class="block rounded-md bg-gray-500 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
        <button onClick="document.getElementById('form').submit();"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Save
        </button> --}}
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.permissions.update', $user) }}">
                @csrf

                <fieldset>
                    {{-- <legend class="sr-only">Notifications</legend> --}}
                    <div class="space-y-5">
                    @foreach (config('permissions') as $key => $permission)
                      <div class="flex gap-3">
                        <div class="flex h-6 shrink-0 items-center">
                          <div class="group grid size-4 grid-cols-1">
                            <input id="{{$permission['label']}}" type="checkbox" name="permissions[]" value="{{ $permission['label'] }}"
                                {{ in_array($permission['label'], $user->permissions ?? []) ? 'checked' : '' }}>
                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                              {{-- <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /> --}}
                              <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                        </div>
                        <div class="text-sm/6">
                          <label for="{{$permission['label']}}" class="font-medium text-gray-900">{{ $permission['label'] }}</label>
                          <p id="comments-description" class="text-gray-500">{{ $permission['description'] }}</p>
                        </div>
                      </div>
                    @endforeach
                    </div>
                  </fieldset>

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
