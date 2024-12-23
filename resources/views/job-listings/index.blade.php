<x-app-layout>
    <x-slot name="header">
        {{ __('Open Positions') }}
    </x-slot>

    <x-slot name="actions">
        @if( Auth::user()->isAdmin() )
            <a href="{{route('job-listings.create')}}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-o-user class="w-4 inline"/> Create New Listing
            </a>
        @endif
        {{-- <a href="{{route('hours.create', Auth::user()->id)}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-clock class="w-4 inline"/> Log New Hours
        </a> --}}
    </x-slot>

    <div class="{{--py-12--}}">
        {{ $jobListings->links() }}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <ul role="list" class="divide-y divide-gray-100">
                @forelse($jobListings as $listing)
                <li class="flex items-center justify-between gap-x-6 py-5">
                  <div class="min-w-0">
                    <div class="flex items-start gap-x-3">
                      <p class="text-sm/6 font-semibold text-gray-900">{{$listing->position_title}}</p>
                      @if($listing->visibility == 'draft')
                      <p class="mt-0.5 whitespace-nowrap rounded-md bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">Draft</p>
                      @elseif($listing->visibility == 'public')
                    <p class="mt-0.5 whitespace-nowrap rounded-md bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Public</p>
                      @else
                      <p class="mt-0.5 whitespace-nowrap rounded-md bg-orange-50 px-1.5 py-0.5 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">Internal</p>
                      @endif
                    </div>
                    <div class="mt-1 flex items-center gap-x-2 text-xs/5 text-gray-500">
                        @if(isset($listing->closing_date))
                      <p class="whitespace-nowrap">Closes <time datetime="{{$listing->closing_date}}">{{\Carbon\Carbon::parse($listing->closing_date)->diffForHumans()}}</time></p>
                      @else
                      <p class="whitespace-nowrap">Open Until Filled</p>
                      @endif
                      <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                        <circle cx="1" cy="1" r="1" />
                      </svg>
                      <p class="truncate">{{$listing->department->name}} for {{$listing->department->sector->name}}</p>
                      <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                        <circle cx="1" cy="1" r="1" />
                      </svg>
                      <p>Seeking {{$listing->number_of_openings}} individuals</p>
                    </div>
                  </div>
                  <div class="flex flex-none items-center gap-x-4">
                    @if($listing->visibility == 'draft' && Auth::user()->isAdmin())
                      <a href="{{route('job-listings.edit', $listing->id)}}" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block"><x-heroicon-o-pencil class="w-3 inline"/> Edit Draft</a>
                    @endif
                    <a href="{{route('job-listings.show', $listing->id)}}" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">View Position</a>
                    {{-- <div class="relative flex-none">
                      <button type="button" class="-m-2.5 block p-2.5 text-gray-500 hover:text-gray-900" id="options-menu-0-button" aria-expanded="false" aria-haspopup="true">
                        <span class="sr-only">Open options</span>
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                          <path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM11.5 15.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                        </svg>
                      </button>
              
                      <!--
                        Dropdown menu, show/hide based on menu state.
              
                        Entering: "transition ease-out duration-100"
                          From: "transform opacity-0 scale-95"
                          To: "transform opacity-100 scale-100"
                        Leaving: "transition ease-in duration-75"
                          From: "transform opacity-100 scale-100"
                          To: "transform opacity-0 scale-95"
                      -->
                      <div class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="options-menu-0-button" tabindex="-1">
                        <!-- Active: "bg-gray-50 outline-none", Not Active: "" -->
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-0">Edit<span class="sr-only">, GraphQL API</span></a>
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-1">Move<span class="sr-only">, GraphQL API</span></a>
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-2">Delete<span class="sr-only">, GraphQL API</span></a>
                      </div>
                    </div> --}}
                  </div>
                </li>
                @empty
                <div class="text-center py-6">
                    <x-heroicon-o-information-circle class="w-12 mx-auto text-gray-400"/>
                    {{-- <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                      <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg> --}}
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No Positions Open</h3>
                    <p class="mt-1 text-sm text-gray-500">Maybe there'll be more later!</p>
                  </div>
                @endforelse
              </ul>  
        </div>
        {{ $jobListings->links() }}
    </div>

</x-app-layout>
