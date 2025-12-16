<x-guestv2-layout>
    @php
        $ogTitle = app_name() . ' - Submit Volunteer Hours';
        $ogDescription = 'Submit your volunteer hours for ' . $user->name;
        $ogImage = app_logo();
        $ogUrl = url()->current();
        $ogType = 'website';
    @endphp
    
    <x-slot name="ogTitle">{{ $ogTitle }}</x-slot>
    <x-slot name="ogDescription">{{ $ogDescription }}</x-slot>
    <x-slot name="ogImage">{{ $ogImage }}</x-slot>
    <x-slot name="ogUrl">{{ $ogUrl }}</x-slot>
    <x-slot name="ogType">{{ $ogType }}</x-slot>

    <div class="relative isolate pt-14">
        <div class="py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                        Submit Volunteer Hours
                    </h1>
                    <p class="mt-2 text-lg text-gray-600">
                        Hi {{ $user->name }}! Use this form to report your volunteer hours.
                    </p>
                </div>

                <!-- Current Fiscal Year Hours Display -->
                @if($currentFiscalLedger)
                    <div class="mx-auto mt-6 max-w-2xl">
                        <div class="rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-4 text-center shadow-lg">
                            <div class="text-xs font-medium text-indigo-100 uppercase tracking-wide">
                                {{ $currentFiscalLedger->name ?? 'Current Fiscal Year' }}
                            </div>
                            <div class="mt-1 text-3xl font-bold text-white">
                                {{ number_format($currentFiscalYearHours, 1) }}
                            </div>
                            <div class="mt-0.5 text-sm text-indigo-100">
                                {{ $currentFiscalYearHours == 1 ? 'hour' : 'hours' }} logged
                            </div>
                            <div class="mt-2 text-xs text-indigo-200">
                                {{ \Carbon\Carbon::parse($currentFiscalLedger->start_date)->format('M j, Y') }} - 
                                {{ \Carbon\Carbon::parse($currentFiscalLedger->end_date)->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mx-auto mt-6 max-w-2xl rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {!! session('success')['message'] !!}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mx-auto mt-10 max-w-2xl">
                    <form action="{{ route('hours.public.store', ['token' => $token]) }}" method="POST" class="space-y-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                        @csrf
                        
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8">
                                <!-- Short Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900">
                                        Short Description
                                    </label>
                                    <div class="mt-2">
                                        <input 
                                            type="text" 
                                            name="description" 
                                            id="description" 
                                            value="{{ old('description') }}"
                                            placeholder="e.g., Picnic Volunteer, Badge Checking at FM2024"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                        <p class="mt-2 text-xs text-gray-500">
                                            This helps us understand what you were doing. You can be descriptive or general.
                                        </p>
                                        @error('description')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Volunteer Date -->
                                <div>
                                    <label for="volunteer_date" class="block text-sm font-medium leading-6 text-gray-900">
                                        Volunteer Date <span class="text-red-600">*</span>
                                    </label>
                                    <div class="mt-2">
                                        <input 
                                            type="date" 
                                            name="volunteer_date" 
                                            id="volunteer_date" 
                                            value="{{ old('volunteer_date') }}"
                                            required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                        @error('volunteer_date')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label for="primary_dept_id" class="block text-sm font-medium leading-6 text-gray-900">
                                        Department <span class="text-red-600">*</span>
                                    </label>
                                    <div class="mt-2">
                                        <select 
                                            name="primary_dept_id" 
                                            id="primary_dept_id" 
                                            required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        >
                                            <option value="">Select Department</option>
                                            
                                            @if($recentDepartments->isNotEmpty())
                                                <optgroup label="Recent Departments">
                                                    @foreach($recentDepartments as $department)
                                                        <option value="{{ $department->id }}" {{ old('primary_dept_id') == $department->id ? 'selected' : '' }}>
                                                            {{ $department->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            
                                            @foreach($sectors as $sector)
                                                <optgroup label="{{ $sector->name }}">
                                                    @foreach($sector->departments as $department)
                                                        <option value="{{ $department->id }}" {{ old('primary_dept_id', $user->primary_dept_id) == $department->id ? 'selected' : '' }}>
                                                            {{ $department->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('primary_dept_id')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Hours -->
                                <div>
                                    <label for="hours" class="block text-sm font-medium leading-6 text-gray-900">
                                        Hours <span class="text-red-600">*</span>
                                    </label>
                                    <div class="mt-2">
                                        <input 
                                            type="number" 
                                            name="hours" 
                                            id="hours" 
                                            step="0.1"
                                            min="0"
                                            value="{{ old('hours') }}"
                                            placeholder="2.5"
                                            required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                        <p class="mt-2 text-xs text-gray-500">
                                            Example: An hour and a half would be 1.5
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Quick Set:
                                            <button type="button" class="text-indigo-600 hover:text-indigo-800 px-1" onclick="setInputValue(0.5)">0.5hr</button>
                                            <button type="button" class="text-indigo-600 hover:text-indigo-800 px-1" onclick="setInputValue(1)">1hr</button>
                                            <button type="button" class="text-indigo-600 hover:text-indigo-800 px-1" onclick="setInputValue(2)">2hr</button>
                                            <button type="button" class="text-indigo-600 hover:text-indigo-800 px-1" onclick="setInputValue(4)">4hr</button>
                                        </p>
                                        @error('hours')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">
                                        Notes (Optional)
                                    </label>
                                    <div class="mt-2">
                                        <textarea 
                                            name="notes" 
                                            id="notes" 
                                            rows="4"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        >{{ old('notes') }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500">
                                            This is entirely optional but provided if your volunteer shift requires some contextual information.
                                        </p>
                                        @error('notes')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                            <button 
                                type="submit"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                Submit Hours
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>This link expires on {{ $user->token_expires_at->format('F j, Y') }}</p>
                    </div>
                </div>

                <!-- Recent Hour Submissions -->
                @if($recentHours->isNotEmpty())
                    <div class="mx-auto mt-12 max-w-2xl">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Recent Submissions</h2>
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
                            <ul role="list" class="divide-y divide-gray-100">
                                @foreach($recentHours as $hour)
                                    <li class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg">
                                                            {{ number_format($hour->hours, 1) }}
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            {{ $hour->description ?? 'Volunteer Hours' }}
                                                        </p>
                                                        <div class="mt-1 flex items-center gap-x-2 text-xs text-gray-500">
                                                            <span>{{ \Carbon\Carbon::parse($hour->volunteer_date)->format('M j, Y') }}</span>
                                                            @if($hour->department)
                                                                <span>•</span>
                                                                <span>{{ $hour->department->name }}</span>
                                                            @endif
                                                        </div>
                                                        @if($hour->notes)
                                                            <p class="mt-1 text-xs text-gray-600 line-clamp-2">
                                                                {{ $hour->notes }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 ml-4">
                                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                    {{ $hour->hours == 1 ? '1 hour' : number_format($hour->hours, 1) . ' hours' }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Function to set the value of the hours input field
        function setInputValue(amt) {
            document.getElementById('hours').value = amt;
        }
    </script>
</x-guestv2-layout>
