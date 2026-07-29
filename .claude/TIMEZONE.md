# Per-user timezones & the `<x-user-time>` component

How dates/times get displayed in each user's local timezone, with a
hover popover showing the org's native time. Read this before touching
timezone display logic or adding a new place that shows a date/time.

## Two separate "timezone" concepts — don't confuse them

1. **App/org timezone** — `app_timezone()` (`app/Helpers/helpers.php`).
   The organization's home timezone (Minneapolis-based, `America/Chicago`
   by default from `config/app.php`). Admins can override it site-wide
   under Settings → General (`ApplicationSetting` key `app_timezone`),
   applied in `AppServiceProvider::applyTimezoneOverride()` which sets
   `config('app.timezone')` and `date_default_timezone_set()` on every
   request. This is the timezone Eloquent `datetime` casts come back in
   by default (e.g. `$event->start_time`), and what emails/reminders use.

2. **User timezone** — `user_timezone(?User $user = null)`. A per-user
   *display* preference, stored in `users.timezone` (nullable string,
   migration `2026_07_29_150000_add_timezone_to_users_table.php`).
   `User::effectiveTimezone()` returns `$this->timezone ?: app_timezone()`
   — i.e. falls back to the org timezone if the user hasn't set one.
   `user_timezone()` does the same for `auth()->user()` (or an explicit
   user), and falls back to `app_timezone()` entirely for guests.

Nothing about how times are *stored* changes — only how they're
*displayed*. Business logic (checkin windows, reminder scheduling,
"is this event over") should keep using the raw Carbon instances in the
app timezone; only presentation should call into `user_timezone()`.

## Where the user sets it

`/profile` → Timezone section (`resources/views/profile/partials/update-timezone-form.blade.php`).
- `<select name="timezone">` grouped into "Common" (`common_timezones()`
  — the handful of US zones, pinned at the top since the full
  `DateTimeZone::listIdentifiers()` list is huge) then every other
  region via `grouped_timezones()`.
- Submits to `PATCH /profile/timezone` → `ProfileController@updateTimezone`,
  validated with Laravel's built-in `timezone` rule. An empty value
  clears the override (`null` in the DB), reverting to the org default.
- When `$user->timezone` is set (i.e. different from the default), the
  section grows a right-hand "What to look for" panel (`lg:flex` on the
  `<section>`) explaining the visual cues below, with a **live**
  `<x-user-time :time="now()" .../>` example the user can actually
  hover to try. That panel is intentionally absent for users still on
  the default — there's nothing to explain if nothing is being converted.

## Displaying a time: `<x-user-time>`

`resources/views/components/user-time.blade.php` — an anonymous Blade
component.

```blade
<x-user-time :time="$event->start_time" format="M j, Y g:i A" />
```

- `time` — any Carbon instance (required).
- `format` — a PHP/Carbon `date()` format string (default `'M j, Y g:i A'`).
  Escape literal characters with a backslash, e.g. `'F j, Y \a\t g:i A'`
  for "...at...".

Behavior:
- Converts `time` to `user_timezone()` and renders it with `format`.
- If that timezone **differs** from `app_timezone()`, the rendered text
  gets a globe icon + brand-green dotted underline, and becomes
  hoverable/focusable (Alpine `x-data`) to reveal a compact popover
  labeled "App's Native Time" showing the same instant formatted in the
  org's timezone with the zone abbreviation (e.g. `Jun 20, 2025 8:00 AM CDT`).
- If the user's effective timezone **matches** the app timezone (the
  common case — no override set), it just renders plain text with no
  icon/underline/popover. Don't remove this branch — it's what keeps
  the org-side UI from looking cluttered for everyone who hasn't opted in.

Currently used on the one-off events index, show, and card partial
(`resources/views/one_off_events/{index,show,_event-card}.blade.php`)
for start/end times, check-in time, RSVP date, and check-in window
times. Other pages that show dates/times (e.g. `archived.blade.php`,
`check_ins.blade.php`, `rsvps.blade.php` under `one_off_events/`) still
use raw `->format()` and were intentionally left alone — extend to them
the same way if asked.

## Admin timezone picker vs. this one

`resources/views/settings/index.blade.php` has its own timezone
`<select>` (Settings → General) — that one sets the **org-wide**
`app_timezone`, not a personal preference. It shares `grouped_timezones()`
and `common_timezones()` with the profile picker but is otherwise a
separate, admin-only control (`SettingsController`). Don't merge the two
UIs — they answer different questions ("what timezone is the org in"
vs. "what timezone do *I* want to see times in").

## Adding timezone display to a new view

1. Make sure the value is a Carbon instance (Eloquent `datetime` casts
   already are).
2. Swap `{{ $model->some_time->format('...') }}` for
   `<x-user-time :time="$model->some_time" format="..." />`.
3. Don't wrap it in anything that assumes plain text (e.g. don't rely on
   `strip_tags()` on the output) — the converted case renders an inline
   `<span>` + icon + hidden popover `<div>`, not a bare string.
