@props([
    'morningEmail' => false,
    'morningTelegram' => false,
    'hourEmail' => false,
    'hourTelegram' => false,
    'telegramAvailable' => false,
    'telegramLinked' => false,
    'theme' => 'pending', // 'active' (already RSVP'd, green) or 'pending' (not yet RSVP'd, neutral)
    'optional' => false,
    'disabled' => false, // event is happening now or has already passed, so reminders no longer make sense
])

@php
    $active = $theme === 'active';
    $cellBg = $active ? 'bg-white/60 dark:bg-black/10' : 'bg-white dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700';
    $headText = $active ? 'text-green-800 dark:text-green-200' : 'text-gray-600 dark:text-gray-400';
    $rowText = $active ? 'text-green-800 dark:text-green-200' : 'text-gray-700 dark:text-gray-300';
    $iconText = $active ? 'text-green-600 dark:text-green-400' : 'text-gray-400';
    $toggle = 'w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-green/20 dark:peer-focus:ring-brand-green/40 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[\'\'] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-brand-green peer-disabled:opacity-40 peer-disabled:cursor-not-allowed';
@endphp

<div>
    <div class="flex items-center mb-2.5">
        <x-heroicon-o-bell class="w-4 h-4 mr-1.5 {{ $iconText }}" />
        <p class="text-xs font-semibold uppercase tracking-wide {{ $headText }}">Reminders{{ $optional ? ' (Optional)' : '' }}</p>
    </div>

    <div class="overflow-x-auto rounded-lg {{ $cellBg }} {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left font-normal px-3 py-2 w-0"></th>
                    <th class="text-center font-semibold {{ $headText }} px-3 py-2 text-xs uppercase tracking-wide">
                        <x-heroicon-o-envelope class="w-4 h-4 inline -mt-0.5" /> Email
                    </th>
                    @if($telegramAvailable)
                        <th class="text-center font-semibold {{ $headText }} px-3 py-2 text-xs uppercase tracking-wide">
                            <x-heroicon-o-paper-airplane class="w-4 h-4 inline -mt-0.5" /> Telegram
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-3 py-2 {{ $rowText }} whitespace-nowrap">
                        <x-heroicon-o-sun class="w-4 h-4 inline mr-1.5 {{ $iconText }} -mt-0.5" />Morning of
                    </td>
                    <td class="px-3 py-2 text-center">
                        <label class="relative inline-flex items-center {{ $disabled ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                            <input type="checkbox" name="remind_morning_of_email" value="1" {{ $morningEmail ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }} class="sr-only peer">
                            <div class="{{ $toggle }}"></div>
                        </label>
                    </td>
                    @if($telegramAvailable)
                        <td class="px-3 py-2 text-center">
                            <label class="relative inline-flex items-center {{ ($telegramLinked && !$disabled) ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                <input type="checkbox" name="remind_morning_of_telegram" value="1"
                                    {{ $morningTelegram ? 'checked' : '' }}
                                    {{ ($telegramLinked && !$disabled) ? '' : 'disabled' }}
                                    class="sr-only peer">
                                <div class="{{ $toggle }}"></div>
                            </label>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td class="px-3 py-2 {{ $rowText }} whitespace-nowrap">
                        <x-heroicon-o-clock class="w-4 h-4 inline mr-1.5 {{ $iconText }} -mt-0.5" />1 hour before
                    </td>
                    <td class="px-3 py-2 text-center">
                        <label class="relative inline-flex items-center {{ $disabled ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                            <input type="checkbox" name="remind_hour_before_email" value="1" {{ $hourEmail ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }} class="sr-only peer">
                            <div class="{{ $toggle }}"></div>
                        </label>
                    </td>
                    @if($telegramAvailable)
                        <td class="px-3 py-2 text-center">
                            <label class="relative inline-flex items-center {{ ($telegramLinked && !$disabled) ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                <input type="checkbox" name="remind_hour_before_telegram" value="1"
                                    {{ $hourTelegram ? 'checked' : '' }}
                                    {{ ($telegramLinked && !$disabled) ? '' : 'disabled' }}
                                    class="sr-only peer">
                                <div class="{{ $toggle }}"></div>
                            </label>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>

    @if($disabled)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            <x-heroicon-o-information-circle class="w-3.5 h-3.5 inline -mt-0.5" />
            Reminders aren't available once the event has started.
        </p>
    @elseif($telegramAvailable && !$telegramLinked)
        <p class="mt-2 text-xs {{ $active ? 'text-green-700 dark:text-green-300' : 'text-gray-500 dark:text-gray-400' }}">
            <x-heroicon-o-link class="w-3.5 h-3.5 inline -mt-0.5" />
            <a href="{{ route('profile.edit') }}" class="font-semibold underline hover:no-underline">Link your Telegram account</a>
            to enable reminders there.
        </p>
    @endif
</div>
