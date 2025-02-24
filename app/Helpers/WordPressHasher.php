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
        // Check if the stored hash uses WordPress' portable PHPass format
        if (strlen($storedHash) <= 32) {
            return md5($password) === $storedHash; // Legacy MD5 check
        }

        require_once app_path('Helpers/phpass.php'); // Include the password hashing library
        $wp_hasher = new \PasswordHash(8, true);

        return $wp_hasher->CheckPassword($password, $storedHash);
    }
}
