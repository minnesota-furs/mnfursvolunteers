<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ __("You're logged in!") }}
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Dicta quasi aperiam facere! Blanditiis accusamus minima totam omnis qui eos alias quod, obcaecati in? Necessitatibus iure blanditiis soluta neque? Veritatis, fugit!</p>
        <p class="py-4">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Fuga iure maxime, temporibus rerum odio at omnis deserunt eos ea dolores neque atque debitis natus iste laborum quod, autem voluptas consequatur?</p>
    </x-slot>

</x-app-layout>
