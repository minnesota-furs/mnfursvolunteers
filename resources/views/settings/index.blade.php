<x-app-layout>
    @section('title', 'Application Settings')
    <x-slot name="header">
        {{ __('Application Settings') }}
    </x-slot>

    @if(app_setting('feature_user_tags', false))
        <x-slot name="actions">
            <a href="{{ route('admin.tags.index') }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-tag class="w-4 inline"/> Manage Tags
            </a>
        </x-slot>
    @endif

    <!-- Hidden forms for reset actions (outside main form to avoid nesting) -->
    <form id="reset-logo-form" action="{{ route('settings.reset-logo') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="reset-favicon-form" action="{{ route('settings.reset-favicon') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="settingsForm()">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'branding'" type="button"
                    :class="activeTab === 'branding' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Branding
                </button>
                <button @click="activeTab = 'features'" type="button"
                    :class="activeTab === 'features' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Features
                </button>
                <button @click="activeTab = 'contact'" type="button"
                    :class="activeTab === 'contact' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Contact
                </button>
                <button @click="activeTab = 'security'" type="button"
                    :class="activeTab === 'security' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Security
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="">
            <div class="p-6">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Branding Tab -->
                    <div x-show="activeTab === 'branding'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Branding</h3>
                        
                        <!-- Application Name -->
                        <div class="mb-6">
                            <label for="app_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Application Name
                            </label>
                            <input type="text" name="app_name" id="app_name"
                                value="{{ old('app_name', app_setting('app_name', config('app.name'))) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="MNFursVolunteers">
                            @error('app_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tagline -->
                        <div class="mb-6">
                            <label for="app_tagline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tagline
                            </label>
                            <input type="text" name="app_tagline" id="app_tagline"
                                value="{{ old('app_tagline', app_setting('app_tagline')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Your organization's tagline">
                            @error('app_tagline')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="app_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea name="app_description" id="app_description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Brief description of your organization">{{ old('app_description', app_setting('app_description')) }}</textarea>
                            @error('app_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Logo Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Application Logo
                            </label>
                            
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ app_logo() }}" alt="Current Logo" class="h-16 w-auto border border-gray-200 dark:border-gray-700 rounded p-2 bg-white dark:bg-gray-900">
                                @if(app_setting('app_logo'))
                                    <button type="button" onclick="if(confirm('Reset logo to default?')) document.getElementById('reset-logo-form').submit();" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Reset to default
                                    </button>
                                @endif
                            </div>

                            <input type="file" name="app_logo" id="app_logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                @change="previewImage($event, 'logoPreview')"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-green file:text-white hover:file:bg-brand-green-dark">
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, or SVG. Max 2MB.</p>
                            
                            <div x-show="logoPreview" class="mt-3">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">New Logo Preview:</p>
                                <img x-bind:src="logoPreview" alt="Logo Preview" class="h-16 w-auto border border-gray-300 rounded p-2 bg-white dark:bg-gray-900">
                            </div>

                            @error('app_logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Favicon Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Favicon
                            </label>
                            
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ app_favicon() }}" alt="Current Favicon" class="h-8 w-8 border border-gray-200 dark:border-gray-700 rounded p-1 bg-white dark:bg-gray-900">
                                @if(app_setting('app_favicon'))
                                    <button type="button" onclick="if(confirm('Reset favicon to default?')) document.getElementById('reset-favicon-form').submit();" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Reset to default
                                    </button>
                                @endif
                            </div>

                            <input type="file" name="app_favicon" id="app_favicon" accept="image/x-icon,image/png"
                                @change="previewImage($event, 'faviconPreview')"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-green file:text-white hover:file:bg-brand-green-dark">
                            <p class="mt-1 text-xs text-gray-500">ICO, PNG. Max 512KB. Recommended: 32x32px</p>
                            
                            <div x-show="faviconPreview" class="mt-3">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">New Favicon Preview:</p>
                                <img x-bind:src="faviconPreview" alt="Favicon Preview" class="h-8 w-8 border border-gray-300 rounded p-1 bg-white dark:bg-gray-900">
                            </div>

                            @error('app_favicon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>                        <!-- Colors -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Primary Color
                                </label>
                                <div class="flex items-center gap-2 mt-1">
                                    <input type="color" name="primary_color" id="primary_color"
                                        value="{{ old('primary_color', app_setting('primary_color', '#10b981')) }}"
                                        class="h-10 w-20 rounded border-gray-300 dark:border-gray-600">
                                    <input type="text" x-model="$el.previousElementSibling.value"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="#10b981">
                                </div>
                                @error('primary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Secondary Color
                                </label>
                                <div class="flex items-center gap-2 mt-1">
                                    <input type="color" name="secondary_color" id="secondary_color"
                                        value="{{ old('secondary_color', app_setting('secondary_color', '#3b82f6')) }}"
                                        class="h-10 w-20 rounded border-gray-300 dark:border-gray-600">
                                    <input type="text" x-model="$el.previousElementSibling.value"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="#3b82f6">
                                </div>
                                @error('secondary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Footer Text -->
                        <div class="mb-6">
                            <label for="footer_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Footer Text
                            </label>
                            <input type="text" name="footer_text" id="footer_text"
                                value="{{ old('footer_text', app_setting('footer_text', '© 2001-2025 Minnesota Furs a 501c3 Minnesota Non-Profit • Built and maintained by local furs.')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Footer text displayed at the bottom of all pages">
                            <p class="mt-1 text-xs text-gray-500">Text displayed in the footer of all pages.</p>
                            @error('footer_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Feature Toggles Tab -->
                    <div x-show="activeTab === 'features'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Feature Toggles</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Enable or disable specific features of the application.</p>

                        <div class="space-y-4">
                            <x-feature-toggle 
                                feature="elections" 
                                label="Elections" 
                                description="Board elections and voting system"
                                :beta="feature_is_beta('elections')" />

                            <x-feature-toggle 
                                feature="job_listings" 
                                label="Job Listings" 
                                description="Job postings and applications"
                                :beta="feature_is_beta('job_listings')" />

                            <x-feature-toggle 
                                feature="one_off_events" 
                                label="One-Off Events" 
                                description="Simple event check-ins without shifts with fixed volunteer hours."
                                :beta="feature_is_beta('one_off_events')" />

                            <x-feature-toggle 
                                feature="volunteer_events" 
                                label="Volunteer Events" 
                                description="Full event management with shifts and signups"
                                :beta="feature_is_beta('volunteer_events')" />

                            <x-feature-toggle 
                                feature="wordpress_integration" 
                                label="WordPress Integration" 
                                description="WordPress user authentication and linking"
                                :beta="feature_is_beta('wordpress_integration')" />

                            <x-feature-toggle 
                                feature="user_tags" 
                                label="User Tags" 
                                description="Tagging system for organizing and categorizing users"
                                :beta="feature_is_beta('user_tags')" />
                        </div>
                    </div>

                    <!-- Contact Information Tab -->
                    <div x-show="activeTab === 'contact'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Contact Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Email -->
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Contact Email
                                </label>
                                <input type="email" name="contact_email" id="contact_email"
                                    value="{{ old('contact_email', app_setting('contact_email')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="contact@example.com">
                                @error('contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contact Phone -->
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Contact Phone
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone"
                                    value="{{ old('contact_phone', app_setting('contact_phone')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="(555) 123-4567">
                                @error('contact_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings Tab -->
                    <div x-show="activeTab === 'security'" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Security & Blacklists</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Prevent specific emails or full names from creating accounts. Use comma-separated values.</p>

                        <div class="space-y-6">
                            <!-- Blacklisted Emails -->
                            <div>
                                <label for="blacklist_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Blacklisted Email Addresses
                                </label>
                                <textarea name="blacklist_emails" id="blacklist_emails" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="spam@example.com, abuse@test.com, blocked@domain.com">{{ old('blacklist_emails', app_setting('blacklist_emails')) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Comma-separated list of email addresses that cannot create accounts</p>
                                @error('blacklist_emails')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Blacklisted Full Names -->
                            <div>
                                <label for="blacklist_names" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Blacklisted Full Names (First Last)
                                </label>
                                <textarea name="blacklist_names" id="blacklist_names" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="John Smith, Admin User, Test Account, System Administrator">{{ old('blacklist_names', app_setting('blacklist_names')) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Comma-separated list of full names (First Last) that cannot be used (case-insensitive)</p>
                                @error('blacklist_names')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    <strong>Note:</strong> When a blacklisted value is detected during user creation, the system will display: 
                                    "This [field] is not allowed. Please contact a staff administrator if you believe this is an error."
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions (visible on all tabs) -->
                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 dark:border-gray-700 pt-6 mt-8">
                        <button type="submit" class="rounded-md bg-brand-green px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('settingsForm', () => ({
                activeTab: 'branding',
                logoPreview: null,
                faviconPreview: null,
                
                previewImage(event, targetProperty) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this[targetProperty] = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }))
        })
    </script>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
</x-app-layout>
