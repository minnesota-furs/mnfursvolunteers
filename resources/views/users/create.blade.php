<x-app-layout>
    @auth
        <x-slot name="header">
            Create a new User
            <h2>
                You are currently signed in as <i>Administrator <b>{{Auth::user()->name}}</b></i>
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="max-w-xl">
                    @include('users.partials.create-user-form')
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
