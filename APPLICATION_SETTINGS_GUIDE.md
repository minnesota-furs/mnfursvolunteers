# Application Settings System

## Overview

The Application Settings system provides a flexible way to configure various aspects of the MNFursVolunteers application without modifying code. Administrators can customize branding, enable/disable features, and configure contact information through a user-friendly interface.

## Features

### Branding Customization
- **Application Name**: Change the name displayed throughout the site
- **Tagline**: Set a custom tagline or slogan
- **Description**: Provide an organization description
- **Logo Upload**: Upload a custom logo (PNG, JPG, SVG up to 2MB)
- **Favicon Upload**: Upload a custom favicon (ICO, PNG up to 512KB, recommended 32x32px)
- **Primary Color**: Customize the main brand color
- **Secondary Color**: Set a secondary brand color

### Feature Toggles
Enable or disable entire feature sets:
- **Elections**: Board elections and voting system
- **Job Listings**: Job postings and applications
- **One-Off Events**: Simple event check-ins without shifts
- **Volunteer Events**: Full event management with shifts and signups

### Contact Information
- **Contact Email**: Public contact email address
- **Contact Phone**: Public contact phone number

## Setup Instructions

### 1. Run Migration

Create the settings table in your database:

```bash
php artisan migrate
```

### 2. Seed Default Settings

Populate the database with default settings:

```bash
php artisan db:seed --class=ApplicationSettingsSeeder
```

### 3. Create Storage Link

Ensure the storage link exists for uploaded logos/favicons:

```bash
php artisan storage:link
```

### 4. Access Settings

Navigate to **Settings → Application Settings** (admin only) to configure the application.

## Using Settings in Code

### Helper Functions

The system provides several global helper functions for easy access to settings:

```php
// Get any setting value
$value = app_setting('key_name', 'default_value');

// Get application name
$name = app_name(); // Returns app_name setting or falls back to config('app.name')

// Get logo URL
$logo = app_logo(); // Returns uploaded logo URL or default logo

// Get favicon URL
$favicon = app_favicon(); // Returns uploaded favicon URL or default favicon

// Check if a feature is enabled
if (feature_enabled('elections')) {
    // Elections feature is enabled
}
```

### In Blade Templates

```blade
{{-- Display app name --}}
<h1>{{ app_name() }}</h1>

{{-- Display logo --}}
<img src="{{ app_logo() }}" alt="{{ app_name() }}">

{{-- Conditional feature display --}}
@if(feature_enabled('elections'))
    <a href="{{ route('elections.index') }}">Elections</a>
@endif

{{-- Get any setting --}}
<p>{{ app_setting('app_tagline') }}</p>
```

### In Controllers

```php
use App\Models\ApplicationSetting;

// Get a setting
$appName = ApplicationSetting::get('app_name', 'Default Name');

// Set a setting
ApplicationSetting::set('app_name', 'New Name', 'string', 'The app name', 'branding');

// Get all settings grouped by category
$settings = ApplicationSetting::getAllGrouped();

// Clear settings cache
ApplicationSetting::clearCache();
```

## Database Schema

### `application_settings` Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | string | Unique setting key |
| value | text | Setting value (nullable) |
| type | string | Data type: string, boolean, integer, json, file |
| description | text | Human-readable description |
| group | string | Category: branding, features, contact, general |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update date |

## Available Settings

### Branding Group
- `app_name` (string): Application name
- `app_tagline` (string): Short tagline
- `app_description` (string): Organization description
- `app_logo` (file): Logo file path
- `app_favicon` (file): Favicon file path
- `primary_color` (string): Primary brand color (hex)
- `secondary_color` (string): Secondary brand color (hex)

### Features Group
- `feature_elections` (boolean): Enable elections module
- `feature_job_listings` (boolean): Enable job listings module
- `feature_one_off_events` (boolean): Enable one-off events module
- `feature_volunteer_events` (boolean): Enable volunteer events module

### Contact Group
- `contact_email` (string): Contact email
- `contact_phone` (string): Contact phone

## Adding New Settings

### 1. Via the Interface

Administrators can add settings through the UI at **Settings → Application Settings**.

### 2. Via Code

Add to the seeder (`database/seeders/ApplicationSettingsSeeder.php`):

```php
[
    'key' => 'new_setting_key',
    'value' => 'default value',
    'type' => 'string', // or boolean, integer, json, file
    'description' => 'What this setting does',
    'group' => 'general', // or branding, features, contact
],
```

### 3. Programmatically

```php
ApplicationSetting::set(
    'new_setting_key',
    'value',
    'string', // type
    'Setting description',
    'general' // group
);
```

## Caching

Settings are cached for 1 hour to improve performance. The cache is automatically cleared when settings are updated through the interface.

To manually clear the cache:

```php
ApplicationSetting::clearCache();
```

Or via Artisan:

```bash
php artisan cache:clear
```

## File Uploads

### Logo
- **Accepted formats**: PNG, JPG, SVG
- **Max size**: 2MB
- **Storage**: `storage/app/public/logos/`
- **Recommended**: Transparent PNG, suitable for both light and dark backgrounds

### Favicon
- **Accepted formats**: ICO, PNG
- **Max size**: 512KB
- **Recommended size**: 32x32px or 16x16px
- **Storage**: `storage/app/public/logos/`

Uploaded files are stored in the `public` disk and accessible via `/storage/logos/filename`.

## Feature Toggle Implementation

To conditionally show/hide features based on settings:

### In Navigation

```blade
@if(feature_enabled('elections'))
    <x-nav-link :href="route('elections.index')">
        Elections
    </x-nav-link>
@endif
```

### In Routes

```php
if (feature_enabled('elections')) {
    Route::resource('elections', ElectionController::class);
}
```

### In Controllers

```php
public function index()
{
    if (!feature_enabled('elections')) {
        abort(404);
    }
    
    // Rest of controller logic
}
```

## Security

- Only administrators (users with `isAdmin()` returning true) can access settings
- Settings routes are protected with `isAdmin` middleware
- File uploads are validated for type and size
- Previous logo/favicon files are automatically deleted when replaced

## API Methods

### ApplicationSetting Model

```php
// Static methods
ApplicationSetting::get(string $key, $default = null): mixed
ApplicationSetting::set(string $key, $value, string $type, ?string $description, string $group): ApplicationSetting
ApplicationSetting::getAllGrouped(): Collection
ApplicationSetting::clearCache(): void
ApplicationSetting::getLogo(): string
ApplicationSetting::getFavicon(): string
```

## Troubleshooting

### Settings not appearing
- Ensure migrations have been run: `php artisan migrate`
- Seed default settings: `php artisan db:seed --class=ApplicationSettingsSeeder`

### Uploaded images not showing
- Create storage link: `php artisan storage:link`
- Check file permissions on `storage/app/public/`
- Verify the `public/storage` symlink exists

### Changes not reflecting
- Clear application cache: `php artisan cache:clear`
- Call `ApplicationSetting::clearCache()` programmatically

### Feature toggles not working
- Verify the setting exists in the database
- Check that you're using `feature_enabled('feature_name')` correctly
- Clear cache after changing feature settings

## Future Enhancements

Potential additions to the settings system:

- Email configuration (SMTP settings)
- Social media links
- Analytics tracking codes
- Maintenance mode toggle
- Custom CSS/JavaScript injection
- Multi-language support settings
- Notification preferences
- API rate limiting configuration

---

**Version**: 1.0  
**Last Updated**: October 18, 2025  
**Created By**: GitHub Copilot
