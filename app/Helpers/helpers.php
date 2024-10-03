<?php

if (!function_exists('format_hours')) {
    /**
     * Format hours, hide decimals if even
     *
     * @param  float  $hours
     * @return string
     */
    function format_hours($hours)
    {
        // If the hours are a whole number, show without decimals
        if (floor($hours) == $hours) {
            return number_format($hours, 0);
        }

        // Otherwise, show with 2 decimals
        return number_format($hours, 2);
    }
}
