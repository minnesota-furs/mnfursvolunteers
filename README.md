# MNFursVolunteers

## About mnfursvolunteers

This Project is set to accomplish volunteer & staff tracking with some connections to the Wordpress site for user-associations.

## Project Requirements

- PHP >= 8.2 (with [Composer](https://getcomposer.org/))
- Required [PHP extensions](https://laravel.com/docs/11.x/deployment#server-requirements)
- Node.js 21
- MySQL (Tested with version 8.0.36)

## Quickstart

### Developer Environment (Option 1: Laravel Herd)

The fastest way to get going in a environment is leveriaging the Laravel development environment, [Laravel Herd](https://herd.laravel.com). This is only usable on Windows and OSX developer environments.

> Herd is a blazing fast, native Laravel and PHP development environment for Windows. It provides everything that you need to get started with Laravel development. It ships with PHP, nginx, and Node.js.
>
> You can integrate Herd with Laravel Forge and use a single tool from setting up your site locally to deploying it on a remote server.

Download available @ https://herd.laravel.com/. Setup docs @ https://herd.laravel.com/docs/windows/1/getting-started/installation.

### Developer Environment (Option 2: Laravel Sail)

> [Laravel Sail](https://laravel.com/docs/11.x/sail#introduction) is a light-weight command-line interface for interacting with Laravel's default Docker development environment. Sail provides a great starting point for building a Laravel application using PHP, MySQL, and Redis without requiring prior Docker experience.
>
> At its heart, Sail is the `docker-compose.yml` file and the `sail` script that is stored at the root of your project. The `sail` script provides a CLI with convenient methods for interacting with the Docker containers defined by the `docker-compose.yml` file.

You may run across the "[Laravel Sail Paradox](https://stackoverflow.com/questions/71234071/laravel-sail-paradox-there-are-any-way-to-install-without-php-and-composer-ins)" though because it requires composer, which requires php. If so, you can run this:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

Citation: https://laravel.com/docs/10.x/sail#installing-composer-dependencies-for-existing-projects

Just copy ".env.example" to ".env", change "DB_HOST" (`DB_HOST=mysql`). Once that is done you can now run `./vendor/bin/sail up`. Configuring a [shell alias for sail](https://laravel.com/docs/10.x/sail#configuring-a-shell-alias) would be recommended. Doing so allows you to just run `sail up`.

If you run into permission issues with appending laravel.log; run `sudo chmod -R ugo+rw storage`.

## Seeders and Factories
Popualte your development environment with some test data. The following can be ran via `tinker`:

- Volunteer Events and Shifts: ``

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
