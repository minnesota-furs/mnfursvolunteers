<x-app-layout>

    @auth
        <x-slot name="header">
            Delete a Ledger
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
                    <p>You are currently about to delete ledger <b>{{$ledger->name}}</b></p>

                    @if($hoursCount > 0 || $electionCount > 0)
                        <div class="rounded-md bg-yellow-50 border border-yellow-300 p-4">
                            <p class="text-yellow-800 font-semibold">This ledger has history attached to it:</p>
                            <ul class="list-disc list-inside text-yellow-800">
                                @if($hoursCount > 0)
                                    <li>{{ $hoursCount }} volunteer hour {{ Str::plural('entry', $hoursCount) }}</li>
                                @endif
                                @if($electionCount > 0)
                                    <li>{{ $electionCount }} {{ Str::plural('election', $electionCount) }}</li>
                                @endif
                            </ul>
                            <p class="text-yellow-800 mt-2">Deleting this ledger will not erase that history — it will be safely
                                archived (soft deleted) and simply hidden from ledger lists, so existing hour and election
                                records will still display it correctly.</p>
                        </div>
                    @endif

                    <p>Are you <b><i><u>really</u></i></b> sure you want to do this?</p>

                    <form method="post" action="{{ route('ledger.destroy', $ledger->id) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('delete')

                        <a href="{{route('ledger.show', $ledger->id)}}"
                            class="block rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Cancel
                        </a>
                        <x-primary-button id="submit" class="block justify-center rounded-md bg-red-500 px-0 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-red-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full">
                            {{ __('DELETE LEDGER "') }} {{$ledger->name}} {{ __('"') }}
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
