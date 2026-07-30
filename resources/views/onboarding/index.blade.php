<x-app-layout>
    @section('title', 'Welcome')
    <x-slot name="header">
        {{ __('Welcome to ' . app_name() . '!') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Progress bar --}}
            <nav class="mb-6 px-1" aria-label="Onboarding progress">
                <ol class="flex items-center justify-between">
                    @php
                        $labels = [
                            'profile' => 'Profile',
                            'accessibility' => 'Accessibility',
                            'timezone' => 'Timezone',
                            'calendar' => 'Calendar',
                            'telegram' => 'Telegram',
                        ];
                    @endphp
                    @foreach($steps as $i => $stepKey)
                        @php $stepNumber = $i + 1; @endphp
                        <li class="{{ $loop->last ? 'flex-none' : 'flex-1' }} flex items-center {{ $loop->last ? '' : 'after:content-[\'\'] after:flex-1 after:h-0.5 after:mx-2 ' . ($stepNumber < $step ? 'after:bg-brand-green' : 'after:bg-gray-200 dark:after:bg-gray-700') }}">
                            <div class="flex flex-col items-center gap-1 shrink-0">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold
                                    {{ $stepNumber < $step ? 'bg-brand-green text-white' : ($stepNumber === $step ? 'bg-brand-green/10 text-brand-green border-2 border-brand-green' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500') }}">
                                    @if($stepNumber < $step)
                                        <x-heroicon-s-check class="w-4 h-4" />
                                    @else
                                        {{ $stepNumber }}
                                    @endif
                                </span>
                                <span class="text-xs {{ $stepNumber === $step ? 'font-semibold text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $labels[$stepKey] }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </nav>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    @if($currentStep === 'profile')
                        @include('onboarding.partials.step-profile')
                    @elseif($currentStep === 'accessibility')
                        @include('onboarding.partials.step-accessibility')
                    @elseif($currentStep === 'timezone')
                        @include('onboarding.partials.step-timezone')
                    @elseif($currentStep === 'calendar')
                        @include('onboarding.partials.step-calendar')
                    @elseif($currentStep === 'telegram')
                        @include('onboarding.partials.step-telegram')
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
