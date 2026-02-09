@php
    $user = Auth::user();
    
    // Check what's completed
    $hasLegalNames = !empty($user->first_name) && !empty($user->last_name);
    $hasAlias = !empty($user->name);
    $hasPronouns = !empty($user->pronouns);
    
    // Check if all items are complete
    $isFullyComplete = $hasLegalNames && $hasAlias && $hasPronouns;
    
    // Check if user has dismissed this notice (using session)
    $isDismissed = session('profile_completion_dismissed_until') && 
                   now()->lt(session('profile_completion_dismissed_until'));
@endphp

@if(!$isFullyComplete && !$isDismissed)
<div class="mt-5" x-data="{ show: true }" x-show="show" x-transition>
    <div class="overflow-hidden rounded-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900 dark:to-orange-900 border-2 border-amber-200 dark:border-amber-700 px-4 py-5 shadow-lg sm:p-6 relative">
        <button 
            @click="fetch('{{ route('dashboard.dismiss-profile-notice') }}', { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                } 
            }).then(() => { show = false })"
            class="absolute top-3 right-3 text-amber-600 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-100 text-sm font-medium"
            title="Dismiss for 14 days"
        >
            Dismiss for 14 days
        </button>
        
        <h3 class="text-xl font-bold mb-3 text-amber-800 dark:text-amber-200 flex items-center">
            <x-heroicon-o-user-circle class="w-6 h-6 mr-2" />
            Complete Your Profile!
        </h3>
        
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-amber-100 dark:border-amber-700">
            <p class="text-gray-600 dark:text-gray-300 mb-3">
                Help us get to know you better by completing your profile:
            </p>
            
            <ul class="space-y-2">
                <li class="flex items-center text-gray-700 dark:text-gray-200">
                    @if($hasLegalNames)
                        <x-heroicon-s-check-circle class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                    @else
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" />
                    @endif
                    <span class="{{ $hasLegalNames ? 'font-medium' : '' }}">Legal First and Last Name</span>
                </li>
                
                <li class="flex items-center text-gray-700 dark:text-gray-200">
                    @if($hasAlias)
                        <x-heroicon-s-check-circle class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                    @else
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" />
                    @endif
                    <span class="{{ $hasAlias ? 'font-medium' : '' }}">Display Name/Alias</span>
                </li>
                
                <li class="flex items-center text-gray-700 dark:text-gray-200">
                    @if($hasPronouns)
                        <x-heroicon-s-check-circle class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                    @else
                        <x-heroicon-o-x-circle class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" />
                    @endif
                    <span class="{{ $hasPronouns ? 'font-medium' : '' }}">Pronouns</span>
                </li>
            </ul>
            
            <div class="mt-4">
                <a href="{{ route('profile.edit') }}" 
                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-1" />
                    Update Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endif
