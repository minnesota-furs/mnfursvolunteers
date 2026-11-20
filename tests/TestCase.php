<?php

namespace Tests;

use App\Http\Middleware\CheckSetupCompleted;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * An RSA key pair generated once per test run so Passport can sign tokens
     * without key files in storage/.
     *
     * @var array{private: string, public: string}|null
     */
    private static ?array $passportKeys = null;

    /**
     * Most tests start with an empty users table, which would otherwise send every
     * request to the first-run setup wizard. Tests covering that redirect can call
     * enableSetupRedirect() to restore it.
     *
     * Passport is pointed at an in-memory key pair, see passportKeys().
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(CheckSetupCompleted::class);

        $passportKeys = self::passportKeys();
        config([
            'passport.private_key' => $passportKeys['private'],
            'passport.public_key' => $passportKeys['public'],
        ]);
    }

    /**
     * @return array{private: string, public: string}
     */
    private static function passportKeys(): array
    {
        if (self::$passportKeys === null) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($key, $privateKey);

            self::$passportKeys = [
                'private' => $privateKey,
                'public' => openssl_pkey_get_details($key)['key'],
            ];
        }

        return self::$passportKeys;
    }

    protected function enableSetupRedirect(): static
    {
        $this->app->forgetInstance(CheckSetupCompleted::class);

        return $this;
    }
}
