<x-app-layout>
    <x-slot name="header">
        Delete an existing User
        <h2>
            You are currently signed in as <i>Administrator <b>{{Auth::user()->name}}</b></i>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="max-w-xl">
                @include('users.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
