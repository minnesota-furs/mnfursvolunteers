<x-app-layout>
    <x-slot name="header">
        {{ __('New Hour Log') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="col-span-2">
            {{-- Start Left Column --}}
            <form action="{{ route('hours.store') }}" method="POST">
                @csrf
                <div class="px-4 sm:px-0">
                    <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer Contribution Information</h3>
                </div>
                <div class="mt-6 border-t border-gray-100">
                    <dl class="divide-y divide-gray-100">
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">User</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                @if($selectedUser)
                                    <div>
                                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                        <x-text-input class="block w-64 bg-gray-200 text-sm" type="text" name="" id="" value="{{ $selectedUser->name }}" disabled />
                                        <p class="text-xs text-gray-400">Wrong volunteer selected? <a class="text-blue-400" href="{{route('hours.create')}}">Start over</a>.</p>
                                    </div>
                                @else
                                    <x-select-input name="user_id" id="user_id" class="block text-sm" required>
                                        <option value="">Select a user</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </x-select-input>

                                    <!-- Otherwise, show a dropdown to select the user -->
                                    {{-- <div>
                                        <select name="user_id" id="user_id" required>
                                            <option value="">Select a user</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                @endif
                                {{-- <x-text-input id="name" name="name" type="text" class="block w-64 text-sm" :value="old('name')" required autofocus autocomplete="name" /> --}}
                            </dd>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">Short Description</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-64 text-sm placeholder-gray-300" type="text" name="description" id="description" placeholder="Picnic Volunteer" />
                            </dd>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">Hours</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-64 text-sm" type="number" name="hours" id="hours" step="0.1" required />
                                <p class="text-xs text-gray-400">Quick Set:
                                    <button type="button" class="text-blue-400 px-1" onclick="setInputValue(0.5)">0.5hr</button>
                                    <button type="button" class="text-blue-400 px-1" onclick="setInputValue(1)">1hr</button>
                                    <button type="button" class="text-blue-400 px-1" onclick="setInputValue(2)">2hr</button>
                                    <button type="button" class="text-blue-400 px-1" onclick="setInputValue(4)">4hr</button></p>
                            </dd>
                        </div>
                        {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <x-select-input id="active" name="active" class="block text-sm" required>
                                    <option value="1" {{ old('active', $user->active) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('active', $user->active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </x-select-input>
                            </dd>
                        </div> --}}
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">Notes</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes') }}</x-textarea-input>
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="py-6">
                    <button type="submit" class="block float-right rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                </div>
            </form>
        </div>






        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function () {
            $('user_id').selectize({
                sortField: 'text'
            });
        });

        // Function to set the value of the input field
        function setInputValue(amt) {
            document.getElementById('hours').value = amt;
        }
    </script>
</x-app-layout>

