<x-app-layout>
    @section('title', 'Add Application')
    
    <x-slot name="header">
        {{ __('Add Application') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('job-listings.applicants') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Applications
        </a>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Manually Add Application
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Use this form to add an application that was received via email, phone, or other non-web channels.
                    </p>
                </div>

                <form method="POST" action="{{ route('job-listings.applicants.store') }}" class="space-y-6">
                    @csrf

                    <!-- Position Selection -->
                    <div>
                        <label for="job_listing_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Position <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="job_listing_id" 
                            id="job_listing_id" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('job_listing_id') border-red-300 @enderror"
                        >
                            <option value="">Select a position...</option>
                            @foreach($jobListings as $listing)
                                <option value="{{ $listing->id }}" {{ old('job_listing_id') == $listing->id ? 'selected' : '' }}>
                                    {{ $listing->position_title }} - {{ $listing->department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('job_listing_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Applicant Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Applicant Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('name') border-red-300 @enderror"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('email') border-red-300 @enderror"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Applicant Comments/Message -->
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Applicant's Message/Comments
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Copy their message or summarize what they told you about their interest.
                        </p>
                        <textarea 
                            name="comments" 
                            id="comments" 
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('comments') border-red-300 @enderror"
                            placeholder="Enter the applicant's message or inquiry..."
                        >{{ old('comments') }}</textarea>
                        @error('comments')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Initial Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Initial Status <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="status" 
                            id="status" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('status') border-red-300 @enderror"
                        >
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ old('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="accepted" {{ old('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Internal Note -->
                    <div>
                        <label for="internal_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Internal Note (Optional)
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Add any internal notes about how this application was received or other context for your team.
                        </p>
                        <textarea 
                            name="internal_note" 
                            id="internal_note" 
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('internal_note') border-red-300 @enderror"
                            placeholder="e.g., Received via email on [date], forwarded by [person]..."
                        >{{ old('internal_note') }}</textarea>
                        @error('internal_note')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('job-listings.applicants') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90"
                        >
                            <x-heroicon-o-plus class="w-4 h-4 mr-1.5"/>
                            Create Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
