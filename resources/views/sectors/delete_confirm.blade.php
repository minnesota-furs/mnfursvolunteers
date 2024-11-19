<x-app-layout>

    @auth
        <x-slot name="header">
            Delete a Sector
            <h2>
                You are currently signed in as <i>Administrator <b>{{Auth::user()->name}}</b></i>
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                @if( Auth::user()->isAdmin() )
                    <div class="flex items-center justify-center">
                        <img src="https://cdn3.emoji.gg/emojis/2668_Siren.gif" class="object-contain max-h-[4rem]" alt="Siren GIF">
                        <p class="text-6xl text-red-500 font-bold text-center px-[5%]">!!! Woah there !!!</p>
                        <img src="https://cdn3.emoji.gg/emojis/2668_Siren.gif" class="object-contain max-h-[4rem]" alt="Siren GIF">
                    </div>
                    <p>You are currently about to delete sector <b>{{$sector->name}}</b></p>
                    <p>Are you <b><i><u>really</u></i></b> sure you want to do this?</p>

                    <form method="post" action="{{ route('sectors.destroy', $sector->id) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('delete')

                        <a href="{{route('sectors.show', $sector->id)}}"
                            class="block rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Cancel
                        </a>
                        <x-primary-button id="submit" class="block justify-center rounded-md bg-red-500 px-0 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-red-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full">
                            {{ __('DELETE SECTOR "') }} {{$sector->name}} {{ __('"') }}
                        </x-primary-button>

                    </form>
                @else
                    <p class="text-4xl text-red-500 font-bold">!!! OH NO !!!</p>
                    <p>If you can see this, something has gone terribly wrong.</p>
                    <p>Please report this to those maintaining this website IMMEDIATELY</p>
                @endif
            </div>
        </div>
    @endauth 
</x-app-layout>
