<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display the application settings.
     */
    public function index()
    {
        $settings = ApplicationSetting::getAllGrouped();
        $sysInfo = $this->gatherSystemInfo();
        $hostingInfo = hosting_info();
        $telegramInfo = $this->gatherTelegramInfo();
        $timezones = grouped_timezones();

        return view('settings.index', compact('settings', 'sysInfo', 'hostingInfo', 'telegramInfo', 'timezones'));
    }

    /**
     * Fetch live bot/webhook status from Telegram for the Integrations tab.
     */
    private function gatherTelegramInfo(): ?array
    {
        if (! ApplicationSetting::get('telegram_bot_token')) {
            return null;
        }

        $service = app(TelegramService::class);

        return [
            'bot' => $service->getMe(),
            'webhook' => $service->getWebhookInfo(),
        ];
    }

    /**
     * Gather diagnostic system information for the Information tab.
     */
    private function gatherSystemInfo(): array
    {
        // --- Git info ---
        $gitInfo = ['available' => false];
        try {
            $raw = shell_exec('git -C '.escapeshellarg(base_path()).' log -1 --format="%H|||%h|||%ai|||%s" 2>/dev/null');
            if ($raw) {
                $parts = explode('|||', trim($raw));
                $gitInfo = [
                    'available' => true,
                    'hash' => $parts[0] ?? null,
                    'short_hash' => $parts[1] ?? null,
                    'date' => $parts[2] ?? null,
                    'message' => $parts[3] ?? null,
                ];
            }
        } catch (\Throwable) {
        }

        // --- PHP ---
        $phpExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'gd', 'fileinfo', 'curl', 'zip', 'intl'];
        $loadedExtensions = [];
        foreach ($phpExtensions as $ext) {
            $loadedExtensions[$ext] = extension_loaded($ext);
        }

        // --- Mail ---
        $mailDriver = config('mail.default', 'not set');
        $mailConfig = config("mail.mailers.{$mailDriver}", []);
        $mailHost = $mailConfig['host'] ?? null;
        $mailPort = $mailConfig['port'] ?? null;
        $mailEncrypt = $mailConfig['encryption'] ?? null;
        $mailUser = $mailConfig['username'] ?? null;
        $mailFrom = config('mail.from.address');
        $mailConfigured = ! empty($mailHost) && ! empty($mailUser);

        // --- Database ---
        $dbDriver = config('database.default');
        $dbConfig = config("database.connections.{$dbDriver}", []);
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable) {
        }

        // --- WordPress DB ---
        $wpConfigured = ! empty(config('database.connections.wordpress.host') ?? config('corcel.connection'));
        $wpConnected = false;
        if ($wpConfigured) {
            try {
                DB::connection('wordpress')->getPdo();
                $wpConnected = true;
            } catch (\Throwable) {
            }
        }

        // --- Storage ---
        $storageLinkExists = is_link(public_path('storage'));

        return [
            'git' => $gitInfo,
            'php_version' => PHP_VERSION,
            'php_extensions' => $loadedExtensions,
            'laravel_version' => app()->version(),
            'app_env' => app()->environment(),
            'app_debug' => config('app.debug'),
            'app_timezone' => config('app.timezone'),
            'app_url' => config('app.url'),
            'mail' => [
                'driver' => $mailDriver,
                'host' => $mailHost,
                'port' => $mailPort,
                'encryption' => $mailEncrypt,
                'username' => $mailUser ? '(set)' : '(not set)',
                'from' => $mailFrom,
                'configured' => $mailConfigured,
            ],
            'db' => [
                'driver' => $dbDriver,
                'host' => $dbConfig['host'] ?? null,
                'database' => $dbConfig['database'] ?? null,
                'connected' => $dbConnected,
            ],
            'wordpress_db' => [
                'configured' => $wpConfigured,
                'connected' => $wpConnected,
            ],
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'storage_link' => $storageLinkExists,
            'app_version' => config('app.version', '—'),
        ];
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
            'footer_text' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'app_logo' => 'nullable|image|max:2048',
            'app_favicon' => 'nullable|image|max:512',
            'social_facebook_url' => 'nullable|url|max:500',
            'social_twitter_url' => 'nullable|url|max:500',
            'social_github_url' => 'nullable|url|max:500',
            'social_youtube_url' => 'nullable|url|max:500',
            'social_website_url' => 'nullable|url|max:500',
            'social_twitch_url' => 'nullable|url|max:500',
            'social_discord_url' => 'nullable|url|max:500',
            'disable_public_site' => 'boolean',
            'feature_elections' => 'boolean',
            'feature_job_listings' => 'boolean',
            'feature_one_off_events' => 'boolean',
            'feature_volunteer_events' => 'boolean',
            'feature_perk_tracking' => 'boolean',
            'feature_wordpress_integration' => 'boolean',
            'feature_user_tags' => 'boolean',
            'feature_recognition' => 'boolean',
            'feature_accessibility_disclosures' => 'boolean',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'app_timezone' => 'nullable|timezone',
            'checkin_hours_before' => 'nullable|numeric|min:0|max:48',
            'checkin_hours_after' => 'nullable|numeric|min:0|max:72',
            'blacklist_emails' => 'nullable|string',
            'blacklist_names' => 'nullable|string',
            'warning_email_domains' => 'nullable|string',
            'onboarding_agreement' => 'nullable|string',
            'require_department_for_self_report' => 'boolean',
            'require_invite_code' => 'boolean',
            'advertise_registration_on_login' => 'boolean',
            'require_department_for_user_index' => 'boolean',
            'user_display_name' => 'nullable|string|in:alias,legal_name',
            'telegram_bot_token' => 'nullable|string|max:255',
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

        if ($request->filled('footer_text')) {
            ApplicationSetting::set('footer_text', $request->footer_text, 'string', 'Footer text', 'branding');
        }

        if ($request->filled('primary_color')) {
            ApplicationSetting::set('primary_color', $request->primary_color, 'string', 'Primary brand color', 'branding');
        }

        if ($request->filled('secondary_color')) {
            ApplicationSetting::set('secondary_color', $request->secondary_color, 'string', 'Secondary brand color', 'branding');
        }

        // Social media links
        ApplicationSetting::set('social_facebook_url', $request->input('social_facebook_url', ''), 'string', 'Facebook page URL', 'branding');
        ApplicationSetting::set('social_twitter_url', $request->input('social_twitter_url', ''), 'string', 'X/Twitter profile URL', 'branding');
        ApplicationSetting::set('social_github_url', $request->input('social_github_url', ''), 'string', 'GitHub organization URL', 'branding');
        ApplicationSetting::set('social_youtube_url', $request->input('social_youtube_url', ''), 'string', 'YouTube channel URL', 'branding');
        ApplicationSetting::set('social_website_url', $request->input('social_website_url', ''), 'string', 'Website URL', 'branding');
        ApplicationSetting::set('social_twitch_url', $request->input('social_twitch_url', ''), 'string', 'Twitch channel URL', 'branding');
        ApplicationSetting::set('social_discord_url', $request->input('social_discord_url', ''), 'string', 'Discord server URL', 'branding');

        // Public site toggle
        ApplicationSetting::set('disable_public_site', $request->boolean('disable_public_site'), 'boolean', 'Disable public pages and redirect to dashboard', 'branding');

        // Feature toggles
        ApplicationSetting::set('feature_elections', $request->boolean('feature_elections'), 'boolean', 'Enable/disable elections feature', 'feature_flags');
        ApplicationSetting::set('feature_job_listings', $request->boolean('feature_job_listings'), 'boolean', 'Enable/disable job listings feature', 'feature_flags');
        ApplicationSetting::set('feature_job_applications', $request->boolean('feature_job_applications'), 'boolean', 'Enable/disable job applications feature', 'feature_flags');
        ApplicationSetting::set('feature_one_off_events', $request->boolean('feature_one_off_events'), 'boolean', 'Enable/disable simple volunteer events feature', 'feature_flags');
        ApplicationSetting::set('feature_volunteer_events', $request->boolean('feature_volunteer_events'), 'boolean', 'Enable/disable volunteer events feature', 'feature_flags');
        ApplicationSetting::set('feature_perk_tracking', $request->boolean('feature_perk_tracking'), 'boolean', 'Enable/disable perk tracking feature', 'feature_flags');
        ApplicationSetting::set('feature_wordpress_integration', $request->boolean('feature_wordpress_integration'), 'boolean', 'Enable/disable WordPress integration feature', 'feature_flags');
        ApplicationSetting::set('feature_user_tags', $request->boolean('feature_user_tags'), 'boolean', 'Enable/disable user tags feature', 'feature_flags');
        ApplicationSetting::set('feature_recognition', $request->boolean('feature_recognition'), 'boolean', 'Enable/disable recognition and awards module', 'feature_flags');
        ApplicationSetting::set('feature_accessibility_disclosures', $request->boolean('feature_accessibility_disclosures'), 'boolean', 'Allow volunteers to disclose accessibility needs and show potential shift conflicts', 'feature_flags');

        // Contact information
        if ($request->filled('contact_email')) {
            ApplicationSetting::set('contact_email', $request->contact_email, 'string', 'Contact email address', 'contact');
        }

        if ($request->filled('contact_phone')) {
            ApplicationSetting::set('contact_phone', $request->contact_phone, 'string', 'Contact phone number', 'contact');
        }

        // Timezone override (blank clears it, reverting to config/app.php's timezone)
        if ($request->filled('app_timezone')) {
            ApplicationSetting::set('app_timezone', $request->app_timezone, 'string', 'Overrides config/app.php timezone for the whole application', 'general');
        } else {
            ApplicationSetting::where('key', 'app_timezone')->delete();
            Cache::forget('app_setting_app_timezone');
        }

        // Event settings
        if ($request->filled('checkin_hours_before')) {
            ApplicationSetting::set('checkin_hours_before', $request->checkin_hours_before, 'number', 'Check-in hours before event', 'event_settings');
        }
        if ($request->filled('checkin_hours_after')) {
            ApplicationSetting::set('checkin_hours_after', $request->checkin_hours_after, 'number', 'Check-in hours after event', 'event_settings');
        }

        // Security settings
        ApplicationSetting::set('blacklist_emails', $request->input('blacklist_emails', ''), 'string', 'Blacklisted email addresses', 'security');
        ApplicationSetting::set('blacklist_names', $request->input('blacklist_names', ''), 'string', 'Blacklisted full names', 'security');
        ApplicationSetting::set('warning_email_domains', $request->input('warning_email_domains', ''), 'string', 'Email domains that require registration confirmation', 'volunteers');

        // Onboarding
        ApplicationSetting::set('onboarding_agreement', $request->input('onboarding_agreement', ''), 'string', 'Registration agreement text', 'onboarding');
        ApplicationSetting::set('require_department_for_self_report', $request->boolean('require_department_for_self_report'), 'boolean', 'Require department assignment for self-reported hours', 'volunteers');
        ApplicationSetting::set('require_invite_code', $request->boolean('require_invite_code'), 'boolean', 'Disallow registration without an invite code', 'volunteers');
        ApplicationSetting::set('advertise_registration_on_login', $request->boolean('advertise_registration_on_login'), 'boolean', 'Show a side panel advertising registration on the login page', 'volunteers');
        ApplicationSetting::set('require_department_for_user_index', $request->boolean('require_department_for_user_index'), 'boolean', 'Prevent users without a department from accessing the User index page', 'volunteers');
        ApplicationSetting::set('user_display_name', $request->input('user_display_name', 'alias'), 'string', 'Prominent name displayed on the Users index page', 'volunteers');

        // Clear cache
        ApplicationSetting::clearCache();

        // Telegram bot connection (separate flash so the Integrations tab can report success/failure precisely)
        if ($request->filled('telegram_bot_token')) {
            return $this->connectTelegram(trim($request->telegram_bot_token));
        }

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => 'Settings updated successfully',
            ]);
    }

    /**
     * Save a Telegram bot token, verify it, and point Telegram's webhook at this app.
     */
    private function connectTelegram(string $token)
    {
        ApplicationSetting::set('telegram_bot_token', $token, 'string', 'Telegram bot API token', 'integrations');
        ApplicationSetting::clearCache();

        $service = app(TelegramService::class);
        $me = $service->getMe();

        if (! $me || empty($me['username'])) {
            return redirect()->route('settings.index')
                ->with('error', 'Could not connect to Telegram with that bot token. Double-check it and try again.');
        }

        ApplicationSetting::set('telegram_bot_username', $me['username'], 'string', 'Telegram bot username', 'integrations');

        $webhookSecret = ApplicationSetting::get('telegram_webhook_secret');
        if (! $webhookSecret) {
            $webhookSecret = Str::random(40);
            ApplicationSetting::set('telegram_webhook_secret', $webhookSecret, 'string', 'Telegram webhook secret path segment', 'integrations');
        }

        ApplicationSetting::clearCache();

        // Build the webhook URL from the configured APP_URL rather than route() — route()
        // derives its scheme from however the current request reached this app (e.g. plain
        // http on localhost even when a tunnel like ngrok fronts it with https), but Telegram
        // requires the webhook itself to be https regardless of which host the admin used.
        $webhookUrl = rtrim(config('app.url'), '/').'/'.ltrim(route('telegram.webhook', $webhookSecret, false), '/');

        if (! str_starts_with($webhookUrl, 'https://')) {
            return redirect()->route('settings.index')
                ->with('error', "Connected to @{$me['username']}, but the webhook needs an HTTPS APP_URL. Set APP_URL in .env to your public https:// URL (e.g. your ngrok URL) and save again.");
        }

        $webhookSet = $service->setWebhook($webhookUrl, $webhookSecret);

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => $webhookSet
                    ? "Connected to @{$me['username']} and webhook registered."
                    : "Connected to @{$me['username']}, but registering the webhook failed. This app's URL may not be reachable from the internet.",
            ]);
    }

    /**
     * Disconnect the Telegram bot: remove the webhook and forget the stored credentials.
     */
    public function disconnectTelegram()
    {
        if (ApplicationSetting::get('telegram_bot_token')) {
            app(TelegramService::class)->deleteWebhook();
        }

        // clearCache() only forgets cache entries for keys still present in the table, so
        // deleted keys must be forgotten explicitly first or the stale "connected" value
        // keeps being served from cache for up to an hour.
        foreach (['telegram_bot_token', 'telegram_bot_username', 'telegram_webhook_secret'] as $key) {
            ApplicationSetting::where('key', $key)->delete();
            Cache::forget("app_setting_{$key}");
        }

        return redirect()->route('settings.index')
            ->with('success', [
                'message' => 'Telegram bot disconnected.',
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
                'message' => 'Logo reset to default.',
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
                'message' => 'Favicon reset to default.',
            ]);
    }
}
