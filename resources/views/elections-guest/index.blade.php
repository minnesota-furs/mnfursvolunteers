<x-guestv2-layout
  title="Board Elections - MNFurs"
  ogTitle="MNFurs Board Elections"
  ogDescription="Stay informed about board elections, view candidates, and learn about governance opportunities within our organization."
  ogImage="{{URL('/images/dashboard/image2.jpg')}}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate">
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <h1 class="text-4xl font-semibold tracking-tight sm:text-4xl">Board Elections ({{count($elections)}})</h1>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
                    @forelse($elections as $election)
                    <div class="mb-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                        <div class="px-6 py-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                            <a href="{{route('elections-public.show', $election)}}" class="hover:text-blue-700 dark:hover:text-blue-400">{{ $election->title }}</a>
                                        </h2>
                                        
                                        @if($election->isVotingPeriod())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                Voting Open
                                            </span>
                                        @elseif($election->isNominationPeriod())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Nominations Open
                                            </span>
                                        @elseif($election->isCompleted())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Completed
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($election->description)
                                        <div class="text-gray-600 prose prose-sm max-w-none mb-4">
                                            {!! $election->parsedDescription !!}
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-sm">
                                        <div>
                                            <dt class="font-medium text-gray-500">
                                                @if($election->isVotingPeriod())
                                                    Voting Period
                                                @elseif($election->isNominationPeriod())
                                                    Nomination Period
                                                @else
                                                    Election Period
                                                @endif
                                            </dt>
                                            <dd class="mt-1 text-gray-900">
                                                @if($election->isNominationPeriod() && $election->hasNominationPeriod())
                                                    {{ $election->nomination_start_date->format('M j, Y') }}<br>
                                                    to {{ $election->nomination_end_date->format('M j, Y') }}
                                                @else
                                                    {{ $election->start_date->format('M j, Y') }}<br>
                                                    to {{ $election->end_date->format('M j, Y') }}
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-medium text-gray-500">Positions Available</dt>
                                            <dd class="mt-1 text-gray-900">
                                                {{ $election->max_positions }} {{ Str::plural('position', $election->max_positions) }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-medium text-gray-500">Candidates</dt>
                                            <dd class="mt-1 text-gray-900">
                                                {{ $election->candidates->count() }} {{ Str::plural('candidate', $election->candidates->count()) }}
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-none items-center gap-x-4 ml-6">
                                    <a href="{{route('elections-public.show', $election)}}" 
                                       class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        View Election
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <x-heroicon-o-check-badge class="w-12 h-12 mx-auto text-gray-400 mb-4"/>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No Elections Available</h3>
                        <p class="mt-1 text-sm text-gray-500">There are currently no active elections or nomination periods.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
  </div>

</x-guestv2-layout>