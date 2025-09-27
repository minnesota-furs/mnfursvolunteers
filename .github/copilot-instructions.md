# AI Coding Agent Instructions for MNFursVolunteers

## Project Overview
MNFursVolunteers is a Laravel 10 volunteer management system integrating with WordPress for user authentication. It tracks volunteer hours, manages events with shifts, and provides department-based organization for staff/volunteers.

## Architecture & Domain Model

### Core Entities
- **Users**: Hybrid authentication (local + WordPress via Corcel), departments/sectors, unique `vol_code` generation
- **Events**: Container for volunteer opportunities with shifts, visibility controls, signup dates
- **Shifts**: Time-based volunteer slots within events, capacity management
- **VolunteerHours**: Hour tracking with fiscal year association, department attribution
- **Departments/Sectors**: Organizational structure for users and activities

### WordPress Integration
- Uses `jgrossi/corcel` package for WordPress database connectivity
- Custom `WordPressUserProvider` handles WP authentication with custom password hashing
- Users can be linked to WordPress accounts via `wordpress_user_id` field
- Separate database connection `wordpress` configured in `config/corcel.php`

## Key Development Patterns

### Unique Code Generation
Models requiring unique codes use `GeneratesVolCode` trait:
```php
use App\Models\Concerns\GeneratesVolCode;
// Generates 5-character codes using unambiguous alphabet (no 0/O, 1/I/L)
```

### Audit Logging
Models requiring audit trails use `AuditableObserver`:
- Tracks create/update/delete operations with user context
- Stores JSON changes in `AuditLog` model
- View logs via Admin controllers (e.g., `Admin\EventController@log`)

### Permissions System
- Permission-based access control defined in `config/permissions.php`
- Controllers check permissions using middleware or manual gates
- Admin functionality requires specific permissions (manage-users, manage-events, etc.)

### Route Organization
- Public routes: job listings (`/openings`) and volunteer opportunities (`/volunteering`)
- Admin routes: namespaced under `Admin\` controllers with permission checks
- Auth routes handled by Laravel Breeze with custom WordPress integration

## Development Workflow

### Environment Setup
**Laravel Herd (Windows/macOS)**: Preferred for local development
**Laravel Sail (Docker)**: Cross-platform alternative
```bash
# Sail setup if no PHP/Composer locally
docker run --rm -u "$(id -u):$(id -g)" -v $(pwd):/var/www/html -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
./vendor/bin/sail up
```

### Frontend Build
- Uses Vite with Tailwind CSS, Alpine.js, and EasyMDE
- Build commands: `npm run dev` (development) or `npm run build` (production)
- Assets: `resources/css/app.css`, `resources/js/app.js`, `resources/js/darkmode.js`

### Testing
- Uses Pest PHP testing framework
- Test structure in `tests/Feature/` and `tests/Unit/`
- RefreshDatabase trait automatically applied to Feature tests

## Database Considerations
- Primary database for Laravel application data
- Separate WordPress database connection for user authentication
- Fiscal year tracking for volunteer hours
- Soft deletes on User model

## Special Features
- **Vol Code Generation**: Unique 5-character codes for users (see `GeneratesVolCode` trait)
- **Event Visibility**: Public/unlisted/private events with signup date controls
- **Shift Management**: Capacity tracking with remaining spots calculation
- **Fiscal Year Reporting**: Hour tracking tied to fiscal periods
- **WordPress User Sync**: Bidirectional integration with existing WordPress site

## Code Style Notes
- Follow Laravel conventions and PSR standards
- Use explicit return types where beneficial
- Models use `$fillable` for mass assignment protection
- Controllers organized by functionality (Admin/, Volunteer/, etc.)
- Custom helper functions in `app/Helpers/helpers.php`