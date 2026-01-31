<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the application settings.
     */
    public function index()
    {
        $settings = ApplicationSetting::getAllGrouped();
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_tagline' => 'nullable|string|max:500',
            'app_description' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'app_logo' => 'nullable|image|max:2048',
            'app_favicon' => 'nullable|image|max:512',
            'feature_elections' => 'boolean',
            'feature_job_listings' => 'boolean',
            'feature_one_off_events' => 'boolean',
            'feature_volunteer_events' => 'boolean',
            'feature_wordpress_integration' => 'boolean',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            // Delete old logo if exists
            $oldLogo = ApplicationSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $logoPath = $request->file('app_logo')->store('logos', 'public');
            ApplicationSetting::set('app_logo', $logoPath, 'file', 'Application logo', 'branding');
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            // Delete old favicon if exists
            $oldFavicon = ApplicationSetting::get('app_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $faviconPath = $request->file('app_favicon')->store('logos', 'public');
            ApplicationSetting::set('app_favicon', $faviconPath, 'file', 'Application favicon', 'branding');
        }

        // Save text settings
        if ($request->filled('app_name')) {
            ApplicationSetting::set('app_name', $request->app_name, 'string', 'Application name', 'branding');
        }

        if ($request->filled('app_tagline')) {
            ApplicationSetting::set('app_tagline', $request->app_tagline, 'string', 'Application tagline', 'branding');
        }

        if ($request->filled('app_description')) {
            ApplicationSetting::set('app_description', $request->app_description, 'string', 'Application description', 'branding');
        }

        if ($request->filled('primary_color')) {
            ApplicationSetting::set('primary_color', $request->primary_color, 'string', 'Primary brand color', 'branding');
        }

        if ($request->filled('secondary_color')) {
            ApplicationSetting::set('secondary_color', $request->secondary_color, 'string', 'Secondary brand color', 'branding');
        }

        // Feature toggles
        ApplicationSetting::set('feature_elections', $request->boolean('feature_elections'), 'boolean', 'Enable/disable elections feature', 'feature_flags');
        ApplicationSetting::set('feature_job_listings', $request->boolean('feature_job_listings'), 'boolean', 'Enable/disable job listings feature', 'feature_flags');
        ApplicationSetting::set('feature_one_off_events', $request->boolean('feature_one_off_events'), 'boolean', 'Enable/disable one-off events feature', 'feature_flags');
        ApplicationSetting::set('feature_volunteer_events', $request->boolean('feature_volunteer_events'), 'boolean', 'Enable/disable volunteer events feature', 'feature_flags');
        ApplicationSetting::set('feature_wordpress_integration', $request->boolean('feature_wordpress_integration'), 'boolean', 'Enable/disable WordPress integration feature', 'feature_flags');

        // Contact information
        if ($request->filled('contact_email')) {
            ApplicationSetting::set('contact_email', $request->contact_email, 'string', 'Contact email address', 'contact');
        }

        if ($request->filled('contact_phone')) {
            ApplicationSetting::set('contact_phone', $request->contact_phone, 'string', 'Contact phone number', 'contact');
        }

        // Clear cache
        ApplicationSetting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => "Settings updated successfully",
            ]);
    }

    /**
     * Reset logo to default.
     */
    public function resetLogo()
    {
        $oldLogo = ApplicationSetting::get('app_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $setting = ApplicationSetting::where('key', 'app_logo')->first();
        if ($setting) {
            $setting->delete();
        }

        ApplicationSetting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => "Logo reset to default.",
            ]);
    }

    /**
     * Reset favicon to default.
     */
    public function resetFavicon()
    {
        $oldFavicon = ApplicationSetting::get('app_favicon');
        if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $setting = ApplicationSetting::where('key', 'app_favicon')->first();
        if ($setting) {
            $setting->delete();
        }

        ApplicationSetting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => "Favicon reset to default.",
            ]);
    }
}
