<x-app-layout>
    <x-slot name="header">
        {{ __('Application Settings') }}
    </x-slot>

    <div class="container">
    
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @foreach ($settings as $group => $groupSettings)
                <h2 class="text-xl font-semibold mb-2">{{ ucwords(str_replace('_', ' ', $group)) }}</h2>
                <div class="border border-gray-300 p-4 rounded-md mb-6">
                    @foreach ($groupSettings as $setting)
                        <div class="mb-4">
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700">
                                {{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}
                            </label>
                            @if ($setting->description)
                                <p class="text-sm text-gray-500 mb-2">{{ $setting->description }}</p>
                            @endif
                            @if ($setting->type === 'boolean')
                                <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" class="block w-full mt-1">
                                    <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            @elseif ($setting->type === 'string')
                                <input type="text" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}" class="block w-full mt-1">
                            @elseif ($setting->type === 'integer')
                                <input type="number" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}" class="block w-full mt-1">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Save Settings
            </button>
        </form>
    </div>
</x-app-layout>