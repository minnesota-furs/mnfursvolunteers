<?php

namespace App\Helpers;

/**
 * WordPress password hashing and verification class.
 * Implements the same password hashing mechanism as WordPress.
 */
class WordPressHasher
{
    public static function check($password, $storedHash)
    {
        \Log::debug('  Checking password against stored hash: ' . $storedHash);
        // Check if the stored hash uses WordPress' portable PHPass format
        if (strlen($storedHash) <= 32) {
            return md5($password) === $storedHash; // Legacy MD5 check
        }

        $wp_hasher = new \App\Helpers\PasswordHash(8, false);

        \Log::debug('  Using WordPress hasher for password verification.', [$wp_hasher]);

        return $wp_hasher->CheckPassword($password, $storedHash);
    }
}
