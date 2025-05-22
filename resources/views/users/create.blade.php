<x-app-layout>
    @section('title', 'Users - Create New')
    @auth
        <x-slot name="header">
            Create a new User
        </x-slot>

        
        @include('users.partials.create-user-form')

    @endauth
</x-app-layout>
