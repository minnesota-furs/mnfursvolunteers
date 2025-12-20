<x-guestv2-layout
  title="{{ $election->title }} - Board Elections - MNFurs"
  ogTitle="{{ $election->title }} - MNFurs Board Elections"
  ogDescription="{{ strip_tags($election->parsedDescription) }}"
  ogImage="{{URL('/images/dashboard/image2.jpg')}}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate">
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <!-- Breadcrumb -->
          <nav class="mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
              <li><a href="{{route('elections-public.index')}}" class="hover:text-blue-600">Elections</a></li>
              <li><x-heroicon-s-chevron-right class="w-4 h-4"/></li>
              <li class="text-gray-900">{{ $election->title }}</li>
            </ol>
          </nav>

          <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $election->title }}</h1>
                
                @if($election->isVotingPeriod())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Voting Open
                    </span>
                @elseif($election->isNominationPeriod())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        Nominations Open
                    </span>
                @elseif($election->isCompleted())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        Completed
                    </span>
                @endif
            </div>
            
            @if($election->description)
                <div class="prose prose-lg max-w-none text-gray-600 dark:text-gray-300 mb-8">
                    {!! $election->parsedDescription !!}
                </div>
            @endif
          </div>

          <!-- Election Details -->
          <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700 mb-8">
            <div class="px-6 py-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Election Information</h2>
                
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @if($election->hasNominationPeriod())
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomination Period</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $election->nomination_start_date->format('M j, Y g:i A') }}<br>
                            to {{ $election->nomination_end_date->format('M j, Y g:i A') }}
                        </dd>
                        @if($election->isNominationPeriod())
                            <dd class="mt-1 text-sm text-blue-600 font-medium">Currently accepting nominations</dd>
                        @endif
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Voting Period</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $election->start_date->format('M j, Y g:i A') }}<br>
                            to {{ $election->end_date->format('M j, Y g:i A') }}
                        </dd>
                        @if($election->isVotingPeriod())
                            <dd class="mt-1 text-sm text-green-600 font-medium">Voting is open</dd>
                        @endif
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Positions Available</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $election->max_positions }} {{ Str::plural('position', $election->max_positions) }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Candidates</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $candidates->count() }} {{ Str::plural('candidate', $candidates->count()) }}
                        </dd>
                    </div>
                    
                    @if($election->min_candidate_hours > 0 || $election->min_voter_hours > 0)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Requirements</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($election->min_candidate_hours > 0)
                                Candidates: {{ $election->min_candidate_hours }} volunteer hours<br>
                            @endif
                            @if($election->min_voter_hours > 0)
                                Voters: {{ $election->min_voter_hours }} volunteer hours
                            @endif
                        </dd>
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Self-Nomination</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $election->allow_self_nomination ? 'Allowed' : 'Not allowed' }}
                        </dd>
                    </div>
                </dl>

                @if($election->isVotingPeriod())
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex">
                            <x-heroicon-s-information-circle class="w-5 h-5 text-blue-400 mr-3 mt-0.5 flex-shrink-0"/>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium">Voting is currently open!</p>
                                <p class="mt-1">To participate in this election, please <a href="{{ route('login') }}" class="underline font-medium">log in to your account</a>.</p>
                            </div>
                        </div>
                    </div>
                @elseif($election->isNominationPeriod())
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex">
                            <x-heroicon-s-information-circle class="w-5 h-5 text-green-400 mr-3 mt-0.5 flex-shrink-0"/>
                            <div class="text-sm text-green-700">
                                <p class="font-medium">Nominations are currently open!</p>
                                <p class="mt-1">
                                    @if($election->allow_self_nomination)
                                        To nominate yourself or others, please <a href="{{ route('login') }}" class="underline font-medium">log in to your account</a>.
                                    @else
                                        To nominate candidates, please <a href="{{ route('login') }}" class="underline font-medium">log in to your account</a>.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
          </div>

          <!-- Candidates -->
          @if($candidates->count() > 0)
          <div class="bg-white shadow sm:rounded-lg border border-gray-200">
            <div class="px-6 py-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Candidates</h2>
                
                <div class="space-y-6">
                    @foreach($candidates as $candidate)
                    <div class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ substr($candidate->user->name, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $candidate->user->name }}
                                </h3>
                                
                                @if($candidate->user->department)
                                    <p class="text-sm text-gray-500 mb-3">
                                        {{ $candidate->user->department->name }}
                                        @if($candidate->user->department->sector)
                                            • {{ $candidate->user->department->sector->name }}
                                        @endif
                                    </p>
                                @endif
                                
                                @if($candidate->statement)
                                    <div class="prose prose-sm max-w-none text-gray-600">
                                        {!! $candidate->parsedStatement !!}
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">No candidate statement provided.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
          </div>
          @else
          <div class="bg-white shadow sm:rounded-lg border border-gray-200">
            <div class="px-6 py-12 text-center">
                <x-heroicon-o-user-group class="w-12 h-12 mx-auto text-gray-400 mb-4"/>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Candidates Yet</h3>
                <p class="text-sm text-gray-500">
                    @if($election->isNominationPeriod())
                        Nominations are currently open. Be the first to nominate a candidate!
                    @else
                        No candidates have been nominated for this election.
                    @endif
                </p>
            </div>
          </div>
          @endif

        </div>
    </div>
  </div>

</x-guestv2-layout>