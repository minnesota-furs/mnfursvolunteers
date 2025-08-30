<?php
namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

trait GeneratesVolCode
{
    // Unambiguous alphabet: no 0/O, 1/I/L
    protected const VOLCODE_ALPHABET = '23456789ABCDEF'; // 8 digits + 22 letters = 30 chars
    protected const VOLCODE_LEN = 5;

    protected static function bootGeneratesVolCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->vol_code)) {
                $model->vol_code = static::newUniqueVolCode();
            }
        });
    }

    public static function newUniqueVolCode(): string
    {
        for ($attempts = 0; $attempts < 20; $attempts++) {
            $code = static::makeVolCode();

            // Fast existence guard; unique index remains the final arbiter
            if (! static::query()->where('vol_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to generate a unique vol_code after multiple attempts.');
    }

    protected static function makeVolCode(): string
    {
        $alphabet = static::VOLCODE_ALPHABET;
        $len = strlen($alphabet);
        $out = '';

        for ($i = 0; $i < static::VOLCODE_LEN; $i++) {
            $out .= $alphabet[random_int(0, $len - 1)];
        }

        return $out; // already uppercase
    }
}
